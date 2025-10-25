<?php
/**
 * API des statistiques du tableau de bord
 */

require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$db = Database::getInstance()->getConnection();
$user = getAuthenticatedUser();
$isAdmin = $user['role'] === 'admin';

if ($method === 'GET') {
    try {
        $userId = $user['user_id'];

        // Total des chantiers
        if ($isAdmin) {
            $stmt = $db->query("SELECT COUNT(*) as total FROM chantiers");
        } else {
            $stmt = $db->prepare("SELECT COUNT(*) as total FROM chantiers WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $userId]);
        }
        $totalChantiers = $stmt->fetch()['total'];

        // Chantiers en cours
        if ($isAdmin) {
            $stmt = $db->query("SELECT COUNT(*) as total FROM chantiers WHERE statut = 'en_cours'");
        } else {
            $stmt = $db->prepare("SELECT COUNT(*) as total FROM chantiers WHERE statut = 'en_cours' AND user_id = :user_id");
            $stmt->execute(['user_id' => $userId]);
        }
        $chantiersEnCours = $stmt->fetch()['total'];

        // Budget total
        if ($isAdmin) {
            $stmt = $db->query("SELECT SUM(budget_total) as total FROM chantiers");
        } else {
            $stmt = $db->prepare("SELECT SUM(budget_total) as total FROM chantiers WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $userId]);
        }
        $budgetTotal = $stmt->fetch()['total'] ?? 0;

        // Total des financements versés
        if ($isAdmin) {
            $stmt = $db->query("SELECT SUM(montant_verse) as total FROM chantier_financements");
        } else {
            $stmt = $db->prepare("
                SELECT SUM(cf.montant_verse) as total
                FROM chantier_financements cf
                JOIN chantiers c ON cf.chantier_id = c.id
                WHERE c.user_id = :user_id
            ");
            $stmt->execute(['user_id' => $userId]);
        }
        $totalVerse = $stmt->fetch()['total'] ?? 0;

        jsonResponse([
            'success' => true,
            'stats' => [
                'total_chantiers' => (int)$totalChantiers,
                'chantiers_en_cours' => (int)$chantiersEnCours,
                'budget_total' => (float)$budgetTotal,
                'total_verse' => (float)$totalVerse
            ]
        ]);
    } catch (PDOException $e) {
        jsonError('Database error', 500);
    }
} else {
    jsonError('Method not allowed', 405);
}
