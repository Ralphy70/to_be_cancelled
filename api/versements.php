<?php
/**
 * API de gestion des versements
 */

require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$db = Database::getInstance()->getConnection();
$user = getAuthenticatedUser();

// POST /api/versements.php - Créer un versement
if ($method === 'POST') {
    $input = getJsonInput();

    $financement_id = $input['financement_id'] ?? null;
    $montant = $input['montant'] ?? 0;
    $date_versement = $input['date_versement'] ?? '';
    $reference = $input['reference'] ?? '';
    $notes = $input['notes'] ?? '';

    if (!$financement_id || $montant <= 0 || empty($date_versement)) {
        jsonError('Missing required fields', 400);
    }

    try {
        $db->beginTransaction();

        // Créer le versement
        $stmt = $db->prepare("
            INSERT INTO versements (financement_id, montant, date_versement, reference, notes)
            VALUES (:financement_id, :montant, :date_versement, :reference, :notes)
        ");

        $stmt->execute([
            'financement_id' => $financement_id,
            'montant' => $montant,
            'date_versement' => $date_versement,
            'reference' => $reference,
            'notes' => $notes
        ]);

        // Mettre à jour le montant versé dans le financement
        $stmt = $db->prepare("UPDATE chantier_financements SET montant_verse = montant_verse + :montant WHERE id = :id");
        $stmt->execute(['montant' => $montant, 'id' => $financement_id]);

        $db->commit();

        jsonResponse(['success' => true, 'message' => 'Versement created successfully', 'id' => $db->lastInsertId()], 201);
    } catch (PDOException $e) {
        $db->rollBack();
        jsonError('Database error', 500);
    }
}

else {
    jsonError('Method not allowed', 405);
}
