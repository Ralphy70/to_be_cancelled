<?php
/**
 * Fonctions utilitaires de l'application
 */

/**
 * Vérifie si l'utilisateur est connecté
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Vérifie si l'utilisateur est administrateur
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Redirige vers une page
 */
function redirect($page) {
    header("Location: $page");
    exit();
}

/**
 * Protège une page (nécessite une connexion)
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

/**
 * Protège une page admin
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        redirect('index.php');
    }
}

/**
 * Nettoie une chaîne pour l'affichage HTML
 */
function cleanOutput($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Formate un montant en euros
 */
function formatMontant($montant) {
    return number_format($montant, 2, ',', ' ') . ' €';
}

/**
 * Formate une date
 */
function formatDate($date, $format = 'd/m/Y') {
    if (empty($date)) return '-';
    $datetime = new DateTime($date);
    return $datetime->format($format);
}

/**
 * Calcule le pourcentage
 */
function calculatePercentage($part, $total) {
    if ($total == 0) return 0;
    return round(($part / $total) * 100, 2);
}

/**
 * Affiche un message flash
 */
function setFlashMessage($message, $type = 'info') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

/**
 * Récupère et affiche le message flash
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

/**
 * Génère un token CSRF
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifie le token CSRF
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Traduit le statut en français
 */
function translateStatus($status) {
    $translations = [
        'planification' => 'Planification',
        'en_cours' => 'En cours',
        'suspendu' => 'Suspendu',
        'termine' => 'Terminé',
        'annule' => 'Annulé',
        'en_negociation' => 'En négociation',
        'accorde' => 'Accordé',
        'verse_partiel' => 'Versé partiellement',
        'verse_total' => 'Versé totalement',
        'refuse' => 'Refusé',
        'prevue' => 'Prévue',
        'engagee' => 'Engagée',
        'payee' => 'Payée'
    ];
    return $translations[$status] ?? $status;
}

/**
 * Obtient la classe CSS pour un badge de statut
 */
function getStatusBadgeClass($status) {
    $classes = [
        'planification' => 'badge-secondary',
        'en_cours' => 'badge-primary',
        'suspendu' => 'badge-warning',
        'termine' => 'badge-success',
        'annule' => 'badge-danger',
        'en_negociation' => 'badge-info',
        'accorde' => 'badge-success',
        'verse_partiel' => 'badge-warning',
        'verse_total' => 'badge-success',
        'refuse' => 'badge-danger',
        'prevue' => 'badge-secondary',
        'engagee' => 'badge-warning',
        'payee' => 'badge-success'
    ];
    return $classes[$status] ?? 'badge-secondary';
}
