<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

$page_title = 'Nouveau financeur';
$db = Database::getInstance()->getConnection();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $type = $_POST['type'] ?? 'autre';
    $contact_nom = trim($_POST['contact_nom'] ?? '');
    $contact_email = trim($_POST['contact_email'] ?? '');
    $contact_telephone = trim($_POST['contact_telephone'] ?? '');

    if (empty($nom)) $errors[] = 'Le nom du financeur est obligatoire';

    if (empty($errors)) {
        try {
            $stmt = $db->prepare("
                INSERT INTO financeurs (nom, type, contact_nom, contact_email, contact_telephone)
                VALUES (:nom, :type, :contact_nom, :contact_email, :contact_telephone)
            ");

            $stmt->execute([
                'nom' => $nom,
                'type' => $type,
                'contact_nom' => $contact_nom ?: null,
                'contact_email' => $contact_email ?: null,
                'contact_telephone' => $contact_telephone ?: null
            ]);

            setFlashMessage('Financeur créé avec succès', 'success');
            redirect('financeurs.php');
        } catch (PDOException $e) {
            $errors[] = 'Erreur lors de la création du financeur';
        }
    }
}

include 'includes/header.php';
?>

<div class="mb-2">
    <a href="financeurs.php">&larr; Retour aux financeurs</a>
</div>

<div class="card">
    <div class="card-header">
        <h2>Nouveau financeur</h2>
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

        <form method="POST" action="financeur_add.php">
            <div class="row">
                <div class="col-8">
                    <div class="form-group">
                        <label for="nom" class="form-label">Nom du financeur *</label>
                        <input type="text" id="nom" name="nom" class="form-control" required
                               value="<?php echo cleanOutput($_POST['nom'] ?? ''); ?>">
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label for="type" class="form-label">Type</label>
                        <select id="type" name="type" class="form-control">
                            <option value="banque">Banque</option>
                            <option value="investisseur">Investisseur</option>
                            <option value="subvention">Subvention</option>
                            <option value="fonds_propres">Fonds propres</option>
                            <option value="autre">Autre</option>
                        </select>
                    </div>
                </div>
            </div>

            <h4 class="mt-3 mb-2">Informations de contact</h4>

            <div class="form-group">
                <label for="contact_nom" class="form-label">Nom du contact</label>
                <input type="text" id="contact_nom" name="contact_nom" class="form-control"
                       value="<?php echo cleanOutput($_POST['contact_nom'] ?? ''); ?>">
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label for="contact_email" class="form-label">Email</label>
                        <input type="email" id="contact_email" name="contact_email" class="form-control"
                               value="<?php echo cleanOutput($_POST['contact_email'] ?? ''); ?>">
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label for="contact_telephone" class="form-label">Téléphone</label>
                        <input type="tel" id="contact_telephone" name="contact_telephone" class="form-control"
                               value="<?php echo cleanOutput($_POST['contact_telephone'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-success">Créer le financeur</button>
                <a href="financeurs.php" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
