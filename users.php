<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

requireAdmin();

$page_title = 'Gestion des utilisateurs';
$db = Database::getInstance()->getConnection();

// Récupérer tous les utilisateurs
$stmt = $db->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2>Gestion des utilisateurs</h2>
        <a href="user_add.php" class="btn btn-primary">Nouvel utilisateur</a>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Nom d'utilisateur</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Date de création</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><strong><?php echo cleanOutput($user['username']); ?></strong></td>
                        <td><?php echo cleanOutput($user['email']); ?></td>
                        <td>
                            <span class="badge <?php echo $user['role'] === 'admin' ? 'badge-danger' : 'badge-secondary'; ?>">
                                <?php echo $user['role'] === 'admin' ? 'Administrateur' : 'Utilisateur'; ?>
                            </span>
                        </td>
                        <td><?php echo formatDate($user['created_at'], 'd/m/Y H:i'); ?></td>
                        <td>
                            <a href="user_edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-secondary">Modifier</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
