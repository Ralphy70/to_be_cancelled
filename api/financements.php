<?php
/**
 * API de gestion des financements
 */

require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$db = Database::getInstance()->getConnection();
$user = getAuthenticatedUser();

// GET /api/financements.php?chantier_id=X - Liste des financements d'un chantier
// GET /api/financements.php?id=X - Détails d'un financement
if ($method === 'GET') {
    $financementId = $_GET['id'] ?? null;
    $chantierId = $_GET['chantier_id'] ?? null;

    if ($financementId) {
        try {
            $stmt = $db->prepare("
                SELECT cf.*, f.nom as financeur_nom, f.type as financeur_type
                FROM chantier_financements cf
                LEFT JOIN financeurs f ON cf.financeur_id = f.id
                WHERE cf.id = :id
            ");
            $stmt->execute(['id' => $financementId]);
            $financement = $stmt->fetch();

            if (!$financement) {
                jsonError('Financement not found', 404);
            }

            // Récupérer les versements
            $stmt = $db->prepare("SELECT * FROM versements WHERE financement_id = :financement_id ORDER BY date_versement DESC");
            $stmt->execute(['financement_id' => $financementId]);
            $financement['versements'] = $stmt->fetchAll();

            jsonResponse(['success' => true, 'financement' => $financement]);
        } catch (PDOException $e) {
            jsonError('Database error', 500);
        }
    } else if ($chantierId) {
        try {
            $stmt = $db->prepare("
                SELECT cf.*, f.nom as financeur_nom, f.type as financeur_type
                FROM chantier_financements cf
                LEFT JOIN financeurs f ON cf.financeur_id = f.id
                WHERE cf.chantier_id = :chantier_id
                ORDER BY cf.created_at DESC
            ");
            $stmt->execute(['chantier_id' => $chantierId]);
            $financements = $stmt->fetchAll();

            jsonResponse(['success' => true, 'financements' => $financements]);
        } catch (PDOException $e) {
            jsonError('Database error', 500);
        }
    } else {
        jsonError('chantier_id or id parameter required', 400);
    }
}

// POST /api/financements.php - Créer un financement
else if ($method === 'POST') {
    $input = getJsonInput();

    $chantier_id = $input['chantier_id'] ?? null;
    $financeur_id = $input['financeur_id'] ?? null;
    $montant_prevu = $input['montant_prevu'] ?? 0;
    $montant_verse = $input['montant_verse'] ?? 0;
    $pourcentage_participation = $input['pourcentage_participation'] ?? null;
    $date_accord = $input['date_accord'] ?? null;
    $conditions = $input['conditions'] ?? '';
    $statut = $input['statut'] ?? 'en_negociation';

    if (!$chantier_id || !$financeur_id || $montant_prevu <= 0) {
        jsonError('Missing required fields', 400);
    }

    try {
        $stmt = $db->prepare("
            INSERT INTO chantier_financements (chantier_id, financeur_id, montant_prevu, montant_verse,
                                                pourcentage_participation, date_accord, conditions, statut)
            VALUES (:chantier_id, :financeur_id, :montant_prevu, :montant_verse,
                    :pourcentage_participation, :date_accord, :conditions, :statut)
        ");

        $stmt->execute([
            'chantier_id' => $chantier_id,
            'financeur_id' => $financeur_id,
            'montant_prevu' => $montant_prevu,
            'montant_verse' => $montant_verse,
            'pourcentage_participation' => $pourcentage_participation,
            'date_accord' => $date_accord,
            'conditions' => $conditions,
            'statut' => $statut
        ]);

        jsonResponse(['success' => true, 'message' => 'Financement created successfully', 'id' => $db->lastInsertId()], 201);
    } catch (PDOException $e) {
        jsonError('Database error', 500);
    }
}

else {
    jsonError('Method not allowed', 405);
}
