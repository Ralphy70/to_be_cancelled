<?php
/**
 * Configuration de l'API REST
 */

// Headers CORS pour permettre les requêtes depuis l'application mobile
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=UTF-8');

// Gérer les requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Configuration de la base de données
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'chantiers');
define('DB_USER', 'chantiers');
define('DB_PASS', 'JhebGYv6n8nFF0lO0');
define('DB_CHARSET', 'utf8mb4');

// Configuration JWT
define('JWT_SECRET_KEY', 'votre_cle_secrete_a_changer_en_production');
define('JWT_EXPIRATION', 86400); // 24 heures

// Timezone
date_default_timezone_set('Europe/Paris');

// Gestion des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 0); // À mettre à 0 en production

/**
 * Classe de connexion à la base de données
 */
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database connection failed']);
            exit();
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }
}

/**
 * Fonctions utilitaires pour l'API
 */

// Réponse JSON
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

// Erreur JSON
function jsonError($message, $statusCode = 400) {
    jsonResponse(['error' => $message], $statusCode);
}

// Récupérer le corps de la requête JSON
function getJsonInput() {
    $input = file_get_contents('php://input');
    return json_decode($input, true);
}

// Génération de JWT simple (pour production, utilisez une vraie librairie JWT)
function generateJWT($userId, $username, $role) {
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $payload = json_encode([
        'user_id' => $userId,
        'username' => $username,
        'role' => $role,
        'exp' => time() + JWT_EXPIRATION
    ]);

    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET_KEY, true);
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

    return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
}

// Vérification de JWT
function verifyJWT($jwt) {
    if (!$jwt) {
        return null;
    }

    $tokenParts = explode('.', $jwt);
    if (count($tokenParts) !== 3) {
        return null;
    }

    $header = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[0]));
    $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1]));
    $signatureProvided = $tokenParts[2];

    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET_KEY, true);
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

    if ($base64UrlSignature !== $signatureProvided) {
        return null;
    }

    $payloadData = json_decode($payload, true);

    // Vérifier l'expiration
    if (isset($payloadData['exp']) && $payloadData['exp'] < time()) {
        return null;
    }

    return $payloadData;
}

// Récupérer l'utilisateur authentifié
function getAuthenticatedUser() {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

    if (!$authHeader) {
        jsonError('Authentication required', 401);
    }

    // Format: "Bearer TOKEN"
    $jwt = str_replace('Bearer ', '', $authHeader);
    $user = verifyJWT($jwt);

    if (!$user) {
        jsonError('Invalid or expired token', 401);
    }

    return $user;
}

// Vérifier si l'utilisateur est admin
function requireAdmin() {
    $user = getAuthenticatedUser();
    if ($user['role'] !== 'admin') {
        jsonError('Admin access required', 403);
    }
    return $user;
}
