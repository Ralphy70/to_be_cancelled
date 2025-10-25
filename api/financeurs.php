<?php
/**
 * API de gestion des financeurs
 */

require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$db = Database::getInstance()->getConnection();
$user = getAuthenticatedUser();

// GET /api/financeurs.php - Liste des financeurs
if ($method === 'GET') {
    try {
        $stmt = $db->query("SELECT * FROM financeurs ORDER BY nom");
        $financeurs = $stmt->fetchAll();

        jsonResponse(['success' => true, 'financeurs' => $financeurs]);
    } catch (PDOException $e) {
        jsonError('Database error', 500);
    }
}

// POST /api/financeurs.php - Créer un financeur
else if ($method === 'POST') {
    $input = getJsonInput();

    $nom = $input['nom'] ?? '';
    $type = $input['type'] ?? 'autre';
    $contact_nom = $input['contact_nom'] ?? null;
    $contact_email = $input['contact_email'] ?? null;
    $contact_telephone = $input['contact_telephone'] ?? null;

    if (empty($nom)) {
        jsonError('Nom is required', 400);
    }

    try {
        $stmt = $db->prepare("
            INSERT INTO financeurs (nom, type, contact_nom, contact_email, contact_telephone)
            VALUES (:nom, :type, :contact_nom, :contact_email, :contact_telephone)
        ");

        $stmt->execute([
            'nom' => $nom,
            'type' => $type,
            'contact_nom' => $contact_nom,
            'contact_email' => $contact_email,
            'contact_telephone' => $contact_telephone
        ]);

        jsonResponse(['success' => true, 'message' => 'Financeur created successfully', 'id' => $db->lastInsertId()], 201);
    } catch (PDOException $e) {
        jsonError('Database error', 500);
    }
}

else {
    jsonError('Method not allowed', 405);
}
