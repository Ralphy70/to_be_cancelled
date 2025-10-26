<?php
/**
 * Configuration de l'application
 */

// Configuration de la base de données
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'gestion_chantiers');
define('DB_USER', 'chantiers');
define('DB_PASS', 'JhebGYv6n8nFF0lO0');
define('DB_CHARSET', 'utf8mb4');

// Configuration de l'application
define('APP_NAME', 'Gestion de Chantiers');
define('APP_URL', 'http://localhost/gestion-chantiers');

// Configuration des sessions
define('SESSION_LIFETIME', 3600); // 1 heure

// Démarrage de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Timezone
date_default_timezone_set('Europe/Paris');

// Gestion des erreurs (à désactiver en production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
