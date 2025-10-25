<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

requireAdmin();

$page_title = 'Nouvel utilisateur';
$db = Database::getInstance()->getConnection();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $role = $_POST['role'] ?? 'user';

    // Validation
    if (empty($username)) $errors[] = 'Le nom d\'utilisateur est obligatoire';
    if (empty($email)) $errors[] = 'L\'email est obligatoire';
    if (empty($password)) $errors[] = 'Le mot de passe est obligatoire';
    if ($password !== $password_confirm) $errors[] = 'Les mots de passe ne correspondent pas';
    if (strlen($password) < 6) $errors[] = 'Le mot de passe doit contenir au moins 6 caractères';

    // Vérifier si l'utilisateur existe déjà
    if (empty($errors)) {
        $stmt = $db->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
        $stmt->execute(['username' => $username, 'email' => $email]);
        if ($stmt->fetch()) {
            $errors[] = 'Ce nom d\'utilisateur ou cet email existe déjà';
        }
    }

    if (empty($errors)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $db->prepare("
                INSERT INTO users (username, email, password, role)
                VALUES (:username, :email, :password, :role)
            ");

            $stmt->execute([
                'username' => $username,
                'email' => $email,
                'password' => $hashed_password,
                'role' => $role
            ]);

            setFlashMessage('Utilisateur créé avec succès', 'success');
            redirect('users.php');
        } catch (PDOException $e) {
            $errors[] = 'Erreur lors de la création de l\'utilisateur';
        }
    }
}

include 'includes/header.php';
?>

<div class="mb-2">
    <a href="users.php">&larr; Retour aux utilisateurs</a>
</div>

<div class="card">
    <div class="card-header">
        <h2>Nouvel utilisateur</h2>
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

        <form method="POST" action="user_add.php">
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

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label for="password" class="form-label">Mot de passe *</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                        <small class="text-muted">Minimum 6 caractères</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label for="password_confirm" class="form-label">Confirmer le mot de passe *</label>
                        <input type="password" id="password_confirm" name="password_confirm" class="form-control" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="role" class="form-label">Rôle</label>
                <select id="role" name="role" class="form-control">
                    <option value="user">Utilisateur</option>
                    <option value="admin">Administrateur</option>
                </select>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-success">Créer l'utilisateur</button>
                <a href="users.php" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
