<?php
/**
 * API de gestion des dépenses
 */

require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$db = Database::getInstance()->getConnection();
$user = getAuthenticatedUser();

// POST /api/depenses.php - Créer une dépense
if ($method === 'POST') {
    $input = getJsonInput();

    $poste_id = $input['poste_id'] ?? null;
    $description = $input['description'] ?? '';
    $montant = $input['montant'] ?? 0;
    $date_depense = $input['date_depense'] ?? '';
    $fournisseur = $input['fournisseur'] ?? '';
    $numero_facture = $input['numero_facture'] ?? '';
    $statut = $input['statut'] ?? 'prevue';

    if (!$poste_id || empty($description) || $montant <= 0 || empty($date_depense)) {
        jsonError('Missing required fields', 400);
    }

    try {
        $stmt = $db->prepare("
            INSERT INTO depenses (poste_id, description, montant, date_depense, fournisseur, numero_facture, statut)
            VALUES (:poste_id, :description, :montant, :date_depense, :fournisseur, :numero_facture, :statut)
        ");

        $stmt->execute([
            'poste_id' => $poste_id,
            'description' => $description,
            'montant' => $montant,
            'date_depense' => $date_depense,
            'fournisseur' => $fournisseur,
            'numero_facture' => $numero_facture,
            'statut' => $statut
        ]);

        // Mettre à jour le budget consommé du poste
        if ($statut !== 'prevue') {
            $stmt = $db->prepare("UPDATE postes_budgetaires SET budget_consomme = budget_consomme + :montant WHERE id = :poste_id");
            $stmt->execute(['montant' => $montant, 'poste_id' => $poste_id]);
        }

        jsonResponse(['success' => true, 'message' => 'Depense created successfully', 'id' => $db->lastInsertId()], 201);
    } catch (PDOException $e) {
        jsonError('Database error', 500);
    }
}

// PUT /api/depenses.php?id=X - Modifier une dépense
else if ($method === 'PUT') {
    $depenseId = $_GET['id'] ?? null;
    if (!$depenseId) {
        jsonError('Depense ID required', 400);
    }

    $input = getJsonInput();

    try {
        // Récupérer l'ancienne dépense
        $stmt = $db->prepare("SELECT * FROM depenses WHERE id = :id");
        $stmt->execute(['id' => $depenseId]);
        $oldDepense = $stmt->fetch();

        if (!$oldDepense) {
            jsonError('Depense not found', 404);
        }

        // Mettre à jour la dépense
        $stmt = $db->prepare("
            UPDATE depenses
            SET description = :description, montant = :montant, date_depense = :date_depense,
                fournisseur = :fournisseur, numero_facture = :numero_facture, statut = :statut
            WHERE id = :id
        ");

        $stmt->execute([
            'description' => $input['description'],
            'montant' => $input['montant'],
            'date_depense' => $input['date_depense'],
            'fournisseur' => $input['fournisseur'],
            'numero_facture' => $input['numero_facture'],
            'statut' => $input['statut'],
            'id' => $depenseId
        ]);

        // Recalculer le budget consommé
        $stmt = $db->prepare("
            SELECT SUM(montant) as total FROM depenses
            WHERE poste_id = :poste_id AND statut != 'prevue'
        ");
        $stmt->execute(['poste_id' => $oldDepense['poste_id']]);
        $total = $stmt->fetch()['total'] ?? 0;

        $stmt = $db->prepare("UPDATE postes_budgetaires SET budget_consomme = :total WHERE id = :poste_id");
        $stmt->execute(['total' => $total, 'poste_id' => $oldDepense['poste_id']]);

        jsonResponse(['success' => true, 'message' => 'Depense updated successfully']);
    } catch (PDOException $e) {
        jsonError('Database error', 500);
    }
}

else {
    jsonError('Method not allowed', 405);
}
