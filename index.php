<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

$page_title = 'Tableau de bord';
$db = Database::getInstance()->getConnection();

// Récupérer les statistiques
$user_id = $_SESSION['user_id'];
$is_admin = isAdmin();

// Total des chantiers
if ($is_admin) {
    $stmt = $db->query("SELECT COUNT(*) as total FROM chantiers");
} else {
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM chantiers WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
}
$total_chantiers = $stmt->fetch()['total'];

// Chantiers en cours
if ($is_admin) {
    $stmt = $db->query("SELECT COUNT(*) as total FROM chantiers WHERE statut = 'en_cours'");
} else {
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM chantiers WHERE statut = 'en_cours' AND user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
}
$chantiers_en_cours = $stmt->fetch()['total'];

// Budget total
if ($is_admin) {
    $stmt = $db->query("SELECT SUM(budget_total) as total FROM chantiers");
} else {
    $stmt = $db->prepare("SELECT SUM(budget_total) as total FROM chantiers WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
}
$budget_total = $stmt->fetch()['total'] ?? 0;

// Total des financements
if ($is_admin) {
    $stmt = $db->query("SELECT SUM(montant_verse) as total FROM chantier_financements");
} else {
    $stmt = $db->prepare("
        SELECT SUM(cf.montant_verse) as total
        FROM chantier_financements cf
        JOIN chantiers c ON cf.chantier_id = c.id
        WHERE c.user_id = :user_id
    ");
    $stmt->execute(['user_id' => $user_id]);
}
$total_verse = $stmt->fetch()['total'] ?? 0;

// Derniers chantiers
if ($is_admin) {
    $stmt = $db->query("
        SELECT c.*, u.username
        FROM chantiers c
        LEFT JOIN users u ON c.user_id = u.id
        ORDER BY c.created_at DESC
        LIMIT 5
    ");
} else {
    $stmt = $db->prepare("
        SELECT c.*, u.username
        FROM chantiers c
        LEFT JOIN users u ON c.user_id = u.id
        WHERE c.user_id = :user_id
        ORDER BY c.created_at DESC
        LIMIT 5
    ");
    $stmt->execute(['user_id' => $user_id]);
}
$derniers_chantiers = $stmt->fetchAll();

include 'includes/header.php';
?>

<h1 class="mb-3">Tableau de bord</h1>

<!-- Statistiques -->
<div class="row mb-3">
    <div class="col-3">
        <div class="stat-card">
            <div class="stat-card-value"><?php echo $total_chantiers; ?></div>
            <div class="stat-card-label">Total Chantiers</div>
        </div>
    </div>
    <div class="col-3">
        <div class="stat-card">
            <div class="stat-card-value"><?php echo $chantiers_en_cours; ?></div>
            <div class="stat-card-label">En cours</div>
        </div>
    </div>
    <div class="col-3">
        <div class="stat-card">
            <div class="stat-card-value"><?php echo formatMontant($budget_total); ?></div>
            <div class="stat-card-label">Budget Total</div>
        </div>
    </div>
    <div class="col-3">
        <div class="stat-card">
            <div class="stat-card-value"><?php echo formatMontant($total_verse); ?></div>
            <div class="stat-card-label">Fonds Versés</div>
        </div>
    </div>
</div>

<!-- Derniers chantiers -->
<div class="card">
    <div class="card-header">
        <span>Derniers chantiers</span>
        <a href="chantier_add.php" class="btn btn-primary btn-sm">Nouveau chantier</a>
    </div>
    <div class="card-body">
        <?php if (empty($derniers_chantiers)): ?>
            <p class="text-muted">Aucun chantier enregistré.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Adresse</th>
                        <th>Budget</th>
                        <th>Statut</th>
                        <?php if ($is_admin): ?>
                            <th>Responsable</th>
                        <?php endif; ?>
                        <th>Date début</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($derniers_chantiers as $chantier): ?>
                        <tr>
                            <td><strong><?php echo cleanOutput($chantier['nom']); ?></strong></td>
                            <td><?php echo cleanOutput($chantier['adresse']); ?></td>
                            <td><?php echo formatMontant($chantier['budget_total']); ?></td>
                            <td>
                                <span class="badge <?php echo getStatusBadgeClass($chantier['statut']); ?>">
                                    <?php echo translateStatus($chantier['statut']); ?>
                                </span>
                            </td>
                            <?php if ($is_admin): ?>
                                <td><?php echo cleanOutput($chantier['username']); ?></td>
                            <?php endif; ?>
                            <td><?php echo formatDate($chantier['date_debut']); ?></td>
                            <td>
                                <a href="chantier_view.php?id=<?php echo $chantier['id']; ?>" class="btn btn-sm btn-primary">Voir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php if (!empty($derniers_chantiers)): ?>
        <div class="card-footer text-right">
            <a href="chantiers.php">Voir tous les chantiers &rarr;</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
