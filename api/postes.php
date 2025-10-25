<?php
/**
 * API de gestion des postes budgétaires
 */

require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$db = Database::getInstance()->getConnection();
$user = getAuthenticatedUser();

// GET /api/postes.php?chantier_id=X - Liste des postes d'un chantier
// GET /api/postes.php?id=X - Détails d'un poste
if ($method === 'GET') {
    $posteId = $_GET['id'] ?? null;
    $chantierId = $_GET['chantier_id'] ?? null;

    if ($posteId) {
        try {
            $stmt = $db->prepare("
                SELECT pb.*, c.nom as chantier_nom
                FROM postes_budgetaires pb
                JOIN chantiers c ON pb.chantier_id = c.id
                WHERE pb.id = :id
            ");
            $stmt->execute(['id' => $posteId]);
            $poste = $stmt->fetch();

            if (!$poste) {
                jsonError('Poste not found', 404);
            }

            // Récupérer les sous-postes
            $stmt = $db->prepare("SELECT * FROM postes_budgetaires WHERE parent_id = :parent_id ORDER BY ordre, nom");
            $stmt->execute(['parent_id' => $posteId]);
            $poste['sous_postes'] = $stmt->fetchAll();

            // Récupérer les dépenses
            $stmt = $db->prepare("SELECT * FROM depenses WHERE poste_id = :poste_id ORDER BY date_depense DESC");
            $stmt->execute(['poste_id' => $posteId]);
            $poste['depenses'] = $stmt->fetchAll();

            jsonResponse(['success' => true, 'poste' => $poste]);
        } catch (PDOException $e) {
            jsonError('Database error', 500);
        }
    } else if ($chantierId) {
        try {
            $stmt = $db->prepare("
                SELECT * FROM postes_budgetaires
                WHERE chantier_id = :chantier_id AND parent_id IS NULL
                ORDER BY ordre, nom
            ");
            $stmt->execute(['chantier_id' => $chantierId]);
            $postes = $stmt->fetchAll();

            jsonResponse(['success' => true, 'postes' => $postes]);
        } catch (PDOException $e) {
            jsonError('Database error', 500);
        }
    } else {
        jsonError('chantier_id or id parameter required', 400);
    }
}

// POST /api/postes.php - Créer un poste budgétaire
else if ($method === 'POST') {
    $input = getJsonInput();

    $chantier_id = $input['chantier_id'] ?? null;
    $nom = $input['nom'] ?? '';
    $description = $input['description'] ?? '';
    $budget_alloue = $input['budget_alloue'] ?? 0;
    $parent_id = $input['parent_id'] ?? null;

    if (!$chantier_id || empty($nom)) {
        jsonError('Missing required fields', 400);
    }

    try {
        $stmt = $db->prepare("
            INSERT INTO postes_budgetaires (chantier_id, nom, description, budget_alloue, parent_id)
            VALUES (:chantier_id, :nom, :description, :budget_alloue, :parent_id)
        ");

        $stmt->execute([
            'chantier_id' => $chantier_id,
            'nom' => $nom,
            'description' => $description,
            'budget_alloue' => $budget_alloue,
            'parent_id' => $parent_id
        ]);

        jsonResponse(['success' => true, 'message' => 'Poste created successfully', 'id' => $db->lastInsertId()], 201);
    } catch (PDOException $e) {
        jsonError('Database error', 500);
    }
}

// PUT /api/postes.php?id=X - Modifier un poste
else if ($method === 'PUT') {
    $posteId = $_GET['id'] ?? null;
    if (!$posteId) {
        jsonError('Poste ID required', 400);
    }

    $input = getJsonInput();

    try {
        $stmt = $db->prepare("
            UPDATE postes_budgetaires
            SET nom = :nom, description = :description, budget_alloue = :budget_alloue
            WHERE id = :id
        ");

        $stmt->execute([
            'nom' => $input['nom'],
            'description' => $input['description'],
            'budget_alloue' => $input['budget_alloue'],
            'id' => $posteId
        ]);

        jsonResponse(['success' => true, 'message' => 'Poste updated successfully']);
    } catch (PDOException $e) {
        jsonError('Database error', 500);
    }
}

else {
    jsonError('Method not allowed', 405);
}
