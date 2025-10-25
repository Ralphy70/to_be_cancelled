<?php
/**
 * API de gestion des chantiers
 */

require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$db = Database::getInstance()->getConnection();
$user = getAuthenticatedUser();
$isAdmin = $user['role'] === 'admin';

// GET /api/chantiers.php - Liste des chantiers
// GET /api/chantiers.php?id=X - Détails d'un chantier
if ($method === 'GET') {
    $chantierId = $_GET['id'] ?? null;

    if ($chantierId) {
        // Récupérer un chantier spécifique
        try {
            $stmt = $db->prepare("
                SELECT c.*, u.username
                FROM chantiers c
                LEFT JOIN users u ON c.user_id = u.id
                WHERE c.id = :id
            ");
            $stmt->execute(['id' => $chantierId]);
            $chantier = $stmt->fetch();

            if (!$chantier) {
                jsonError('Chantier not found', 404);
            }

            // Vérifier les droits
            if (!$isAdmin && $chantier['user_id'] != $user['user_id']) {
                jsonError('Access denied', 403);
            }

            // Récupérer les statistiques du chantier
            $stmt = $db->prepare("SELECT SUM(budget_alloue) as total_alloue, SUM(budget_consomme) as total_consomme FROM postes_budgetaires WHERE chantier_id = :id");
            $stmt->execute(['id' => $chantierId]);
            $stats = $stmt->fetch();

            $stmt = $db->prepare("SELECT SUM(montant_prevu) as total_prevu, SUM(montant_verse) as total_verse FROM chantier_financements WHERE chantier_id = :id");
            $stmt->execute(['id' => $chantierId]);
            $financement = $stmt->fetch();

            $chantier['stats'] = [
                'budget_alloue' => $stats['total_alloue'] ?? 0,
                'budget_consomme' => $stats['total_consomme'] ?? 0,
                'financement_prevu' => $financement['total_prevu'] ?? 0,
                'financement_verse' => $financement['total_verse'] ?? 0
            ];

            jsonResponse(['success' => true, 'chantier' => $chantier]);
        } catch (PDOException $e) {
            jsonError('Database error', 500);
        }
    } else {
        // Liste des chantiers
        try {
            if ($isAdmin) {
                $stmt = $db->query("
                    SELECT c.*, u.username
                    FROM chantiers c
                    LEFT JOIN users u ON c.user_id = u.id
                    ORDER BY c.created_at DESC
                ");
            } else {
                $stmt = $db->prepare("
                    SELECT c.*, u.username
                    FROM chantiers c
                    LEFT JOIN users u ON c.user_id = u.id
                    WHERE c.user_id = :user_id
                    ORDER BY c.created_at DESC
                ");
                $stmt->execute(['user_id' => $user['user_id']]);
            }

            $chantiers = $stmt->fetchAll();
            jsonResponse(['success' => true, 'chantiers' => $chantiers]);
        } catch (PDOException $e) {
            jsonError('Database error', 500);
        }
    }
}

// POST /api/chantiers.php - Créer un chantier
else if ($method === 'POST') {
    $input = getJsonInput();

    $nom = $input['nom'] ?? '';
    $description = $input['description'] ?? '';
    $adresse = $input['adresse'] ?? '';
    $date_debut = $input['date_debut'] ?? '';
    $date_fin_prevue = $input['date_fin_prevue'] ?? null;
    $budget_total = $input['budget_total'] ?? 0;
    $statut = $input['statut'] ?? 'planification';

    if (empty($nom) || empty($adresse) || empty($date_debut)) {
        jsonError('Missing required fields', 400);
    }

    try {
        $stmt = $db->prepare("
            INSERT INTO chantiers (nom, description, adresse, date_debut, date_fin_prevue, budget_total, statut, user_id)
            VALUES (:nom, :description, :adresse, :date_debut, :date_fin_prevue, :budget_total, :statut, :user_id)
        ");

        $stmt->execute([
            'nom' => $nom,
            'description' => $description,
            'adresse' => $adresse,
            'date_debut' => $date_debut,
            'date_fin_prevue' => $date_fin_prevue,
            'budget_total' => $budget_total,
            'statut' => $statut,
            'user_id' => $user['user_id']
        ]);

        $chantierId = $db->lastInsertId();

        jsonResponse([
            'success' => true,
            'message' => 'Chantier created successfully',
            'id' => $chantierId
        ], 201);
    } catch (PDOException $e) {
        jsonError('Database error', 500);
    }
}

// PUT /api/chantiers.php?id=X - Modifier un chantier
else if ($method === 'PUT') {
    $chantierId = $_GET['id'] ?? null;
    if (!$chantierId) {
        jsonError('Chantier ID required', 400);
    }

    // Vérifier les droits
    $stmt = $db->prepare("SELECT user_id FROM chantiers WHERE id = :id");
    $stmt->execute(['id' => $chantierId]);
    $chantier = $stmt->fetch();

    if (!$chantier) {
        jsonError('Chantier not found', 404);
    }

    if (!$isAdmin && $chantier['user_id'] != $user['user_id']) {
        jsonError('Access denied', 403);
    }

    $input = getJsonInput();

    try {
        $stmt = $db->prepare("
            UPDATE chantiers
            SET nom = :nom, description = :description, adresse = :adresse,
                date_debut = :date_debut, date_fin_prevue = :date_fin_prevue,
                budget_total = :budget_total, statut = :statut
            WHERE id = :id
        ");

        $stmt->execute([
            'nom' => $input['nom'],
            'description' => $input['description'],
            'adresse' => $input['adresse'],
            'date_debut' => $input['date_debut'],
            'date_fin_prevue' => $input['date_fin_prevue'] ?? null,
            'budget_total' => $input['budget_total'],
            'statut' => $input['statut'],
            'id' => $chantierId
        ]);

        jsonResponse(['success' => true, 'message' => 'Chantier updated successfully']);
    } catch (PDOException $e) {
        jsonError('Database error', 500);
    }
}

// DELETE /api/chantiers.php?id=X - Supprimer un chantier
else if ($method === 'DELETE') {
    $chantierId = $_GET['id'] ?? null;
    if (!$chantierId) {
        jsonError('Chantier ID required', 400);
    }

    // Vérifier les droits
    $stmt = $db->prepare("SELECT user_id FROM chantiers WHERE id = :id");
    $stmt->execute(['id' => $chantierId]);
    $chantier = $stmt->fetch();

    if (!$chantier) {
        jsonError('Chantier not found', 404);
    }

    if (!$isAdmin && $chantier['user_id'] != $user['user_id']) {
        jsonError('Access denied', 403);
    }

    try {
        $stmt = $db->prepare("DELETE FROM chantiers WHERE id = :id");
        $stmt->execute(['id' => $chantierId]);

        jsonResponse(['success' => true, 'message' => 'Chantier deleted successfully']);
    } catch (PDOException $e) {
        jsonError('Database error', 500);
    }
}

else {
    jsonError('Method not allowed', 405);
}
