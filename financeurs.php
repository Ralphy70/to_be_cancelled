<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

$page_title = 'Gestion des financeurs';
$db = Database::getInstance()->getConnection();

// Récupérer tous les financeurs
$stmt = $db->query("SELECT * FROM financeurs ORDER BY nom");
$financeurs = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2>Gestion des financeurs</h2>
        <a href="financeur_add.php" class="btn btn-primary">Nouveau financeur</a>
    </div>
    <div class="card-body">
        <?php if (empty($financeurs)): ?>
            <p class="text-muted text-center">Aucun financeur enregistré. Ajoutez votre premier financeur.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Type</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($financeurs as $financeur): ?>
                        <tr>
                            <td><strong><?php echo cleanOutput($financeur['nom']); ?></strong></td>
                            <td><?php echo cleanOutput($financeur['type']); ?></td>
                            <td><?php echo cleanOutput($financeur['contact_nom']); ?></td>
                            <td><?php echo cleanOutput($financeur['contact_email']); ?></td>
                            <td><?php echo cleanOutput($financeur['contact_telephone']); ?></td>
                            <td>
                                <a href="financeur_edit.php?id=<?php echo $financeur['id']; ?>" class="btn btn-sm btn-secondary">Modifier</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
