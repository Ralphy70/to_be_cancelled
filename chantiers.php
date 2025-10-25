<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

$page_title = 'Gestion des chantiers';
$db = Database::getInstance()->getConnection();
$user_id = $_SESSION['user_id'];
$is_admin = isAdmin();

// Récupérer tous les chantiers
if ($is_admin) {
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
    $stmt->execute(['user_id' => $user_id]);
}
$chantiers = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2>Gestion des chantiers</h2>
        <a href="chantier_add.php" class="btn btn-primary">Nouveau chantier</a>
    </div>
    <div class="card-body">
        <?php if (empty($chantiers)): ?>
            <p class="text-muted text-center">Aucun chantier enregistré. Créez votre premier chantier.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Adresse</th>
                        <th>Budget Total</th>
                        <th>Statut</th>
                        <?php if ($is_admin): ?>
                            <th>Responsable</th>
                        <?php endif; ?>
                        <th>Dates</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($chantiers as $chantier): ?>
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
                            <td>
                                <small>
                                    Début: <?php echo formatDate($chantier['date_debut']); ?><br>
                                    Fin prévue: <?php echo formatDate($chantier['date_fin_prevue']); ?>
                                </small>
                            </td>
                            <td>
                                <a href="chantier_view.php?id=<?php echo $chantier['id']; ?>" class="btn btn-sm btn-primary">Voir</a>
                                <a href="chantier_edit.php?id=<?php echo $chantier['id']; ?>" class="btn btn-sm btn-secondary">Modifier</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
