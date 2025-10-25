<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

requireAdmin();

$page_title = 'Modifier utilisateur';
$db = Database::getInstance()->getConnection();
$errors = [];
$user_id = $_GET['id'] ?? 0;

// Récupérer l'utilisateur
$stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch();

if (!$user) {
    setFlashMessage('Utilisateur introuvable', 'danger');
    redirect('users.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $role = $_POST['role'] ?? 'user';

    // Validation
    if (empty($username)) $errors[] = 'Le nom d\'utilisateur est obligatoire';
    if (empty($email)) $errors[] = 'L\'email est obligatoire';

    // Si un nouveau mot de passe est fourni
    if (!empty($password)) {
        if ($password !== $password_confirm) $errors[] = 'Les mots de passe ne correspondent pas';
        if (strlen($password) < 6) $errors[] = 'Le mot de passe doit contenir au moins 6 caractères';
    }

    // Vérifier si le username/email existe déjà (sauf pour cet utilisateur)
    if (empty($errors)) {
        $stmt = $db->prepare("SELECT id FROM users WHERE (username = :username OR email = :email) AND id != :id");
        $stmt->execute(['username' => $username, 'email' => $email, 'id' => $user_id]);
        if ($stmt->fetch()) {
            $errors[] = 'Ce nom d\'utilisateur ou cet email est déjà utilisé';
        }
    }

    if (empty($errors)) {
        try {
            // Préparer la requête
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("
                    UPDATE users
                    SET username = :username, email = :email, password = :password, role = :role
                    WHERE id = :id
                ");
                $stmt->execute([
                    'username' => $username,
                    'email' => $email,
                    'password' => $hashed_password,
                    'role' => $role,
                    'id' => $user_id
                ]);
            } else {
                $stmt = $db->prepare("
                    UPDATE users
                    SET username = :username, email = :email, role = :role
                    WHERE id = :id
                ");
                $stmt->execute([
                    'username' => $username,
                    'email' => $email,
                    'role' => $role,
                    'id' => $user_id
                ]);
            }

            setFlashMessage('Utilisateur modifié avec succès', 'success');
            redirect('users.php');
        } catch (PDOException $e) {
            $errors[] = 'Erreur lors de la modification de l\'utilisateur';
        }
    }
} else {
    $_POST = $user;
}

include 'includes/header.php';
?>

<div class="mb-2">
    <a href="users.php">&larr; Retour aux utilisateurs</a>
</div>

<div class="card">
    <div class="card-header">
        <h2>Modifier l'utilisateur</h2>
    </div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo cleanOutput($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="user_edit.php?id=<?php echo $user_id; ?>">
            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label for="username" class="form-label">Nom d'utilisateur *</label>
                        <input type="text" id="username" name="username" class="form-control" required
                               value="<?php echo cleanOutput($_POST['username'] ?? ''); ?>">
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" id="email" name="email" class="form-control" required
                               value="<?php echo cleanOutput($_POST['email'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <div class="alert alert-info">
                Laissez les champs mot de passe vides si vous ne souhaitez pas le modifier.
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label for="password" class="form-label">Nouveau mot de passe</label>
                        <input type="password" id="password" name="password" class="form-control">
                        <small class="text-muted">Minimum 6 caractères</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label for="password_confirm" class="form-label">Confirmer le mot de passe</label>
                        <input type="password" id="password_confirm" name="password_confirm" class="form-control">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="role" class="form-label">Rôle</label>
                <select id="role" name="role" class="form-control">
                    <option value="user" <?php echo ($_POST['role'] ?? '') == 'user' ? 'selected' : ''; ?>>Utilisateur</option>
                    <option value="admin" <?php echo ($_POST['role'] ?? '') == 'admin' ? 'selected' : ''; ?>>Administrateur</option>
                </select>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-success">Enregistrer les modifications</button>
                <a href="users.php" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
