<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

$page_title = 'Modifier financeur';
$db = Database::getInstance()->getConnection();
$errors = [];
$financeur_id = $_GET['id'] ?? 0;

// Récupérer le financeur
$stmt = $db->prepare("SELECT * FROM financeurs WHERE id = :id");
$stmt->execute(['id' => $financeur_id]);
$financeur = $stmt->fetch();

if (!$financeur) {
    setFlashMessage('Financeur introuvable', 'danger');
    redirect('financeurs.php');
}

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
                UPDATE financeurs
                SET nom = :nom, type = :type, contact_nom = :contact_nom,
                    contact_email = :contact_email, contact_telephone = :contact_telephone
                WHERE id = :id
            ");

            $stmt->execute([
                'nom' => $nom,
                'type' => $type,
                'contact_nom' => $contact_nom ?: null,
                'contact_email' => $contact_email ?: null,
                'contact_telephone' => $contact_telephone ?: null,
                'id' => $financeur_id
            ]);

            setFlashMessage('Financeur modifié avec succès', 'success');
            redirect('financeurs.php');
        } catch (PDOException $e) {
            $errors[] = 'Erreur lors de la modification du financeur';
        }
    }
} else {
    $_POST = $financeur;
}

include 'includes/header.php';
?>

<div class="mb-2">
    <a href="financeurs.php">&larr; Retour aux financeurs</a>
</div>

<div class="card">
    <div class="card-header">
        <h2>Modifier le financeur</h2>
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

        <form method="POST" action="financeur_edit.php?id=<?php echo $financeur_id; ?>">
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
                            <option value="banque" <?php echo ($_POST['type'] ?? '') == 'banque' ? 'selected' : ''; ?>>Banque</option>
                            <option value="investisseur" <?php echo ($_POST['type'] ?? '') == 'investisseur' ? 'selected' : ''; ?>>Investisseur</option>
                            <option value="subvention" <?php echo ($_POST['type'] ?? '') == 'subvention' ? 'selected' : ''; ?>>Subvention</option>
                            <option value="fonds_propres" <?php echo ($_POST['type'] ?? '') == 'fonds_propres' ? 'selected' : ''; ?>>Fonds propres</option>
                            <option value="autre" <?php echo ($_POST['type'] ?? '') == 'autre' ? 'selected' : ''; ?>>Autre</option>
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
                <button type="submit" class="btn btn-success">Enregistrer les modifications</button>
                <a href="financeurs.php" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
