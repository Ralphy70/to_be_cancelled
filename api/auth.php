<?php
/**
 * API d'authentification
 */

require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$db = Database::getInstance()->getConnection();

// POST /api/auth.php - Connexion
if ($method === 'POST') {
    $input = getJsonInput();

    $username = $input['username'] ?? '';
    $password = $input['password'] ?? '';

    if (empty($username) || empty($password)) {
        jsonError('Username and password are required', 400);
    }

    try {
        $stmt = $db->prepare("SELECT id, username, email, password, role FROM users WHERE username = :username OR email = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $token = generateJWT($user['id'], $user['username'], $user['role']);

            jsonResponse([
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ]
            ]);
        } else {
            jsonError('Invalid credentials', 401);
        }
    } catch (PDOException $e) {
        jsonError('Database error', 500);
    }
}

// GET /api/auth.php - Vérifier le token et récupérer l'utilisateur
else if ($method === 'GET') {
    $user = getAuthenticatedUser();

    try {
        $stmt = $db->prepare("SELECT id, username, email, role FROM users WHERE id = :id");
        $stmt->execute(['id' => $user['user_id']]);
        $userData = $stmt->fetch();

        if ($userData) {
            jsonResponse([
                'success' => true,
                'user' => $userData
            ]);
        } else {
            jsonError('User not found', 404);
        }
    } catch (PDOException $e) {
        jsonError('Database error', 500);
    }
}

else {
    jsonError('Method not allowed', 405);
}
