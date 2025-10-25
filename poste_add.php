<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

$page_title = 'Ajouter un poste budgétaire';
$db = Database::getInstance()->getConnection();
$errors = [];
$chantier_id = $_GET['chantier_id'] ?? 0;
$parent_id = $_GET['parent_id'] ?? null;

// Vérifier que le chantier existe et que l'utilisateur a les droits
$stmt = $db->prepare("SELECT * FROM chantiers WHERE id = :id");
$stmt->execute(['id' => $chantier_id]);
$chantier = $stmt->fetch();

if (!$chantier) {
    setFlashMessage('Chantier introuvable', 'danger');
    redirect('chantiers.php');
}

if (!isAdmin() && $chantier['user_id'] != $_SESSION['user_id']) {
    setFlashMessage('Vous n\'avez pas les droits', 'danger');
    redirect('chantiers.php');
}

// Si c'est une sous-catégorie, récupérer le poste parent
$parent_poste = null;
if ($parent_id) {
    $stmt = $db->prepare("SELECT * FROM postes_budgetaires WHERE id = :id");
    $stmt->execute(['id' => $parent_id]);
    $parent_poste = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $budget_alloue = $_POST['budget_alloue'] ?? 0;

    if (empty($nom)) $errors[] = 'Le nom du poste est obligatoire';

    if (empty($errors)) {
        try {
            $stmt = $db->prepare("
                INSERT INTO postes_budgetaires (chantier_id, nom, description, budget_alloue, parent_id)
                VALUES (:chantier_id, :nom, :description, :budget_alloue, :parent_id)
            ");

            $stmt->execute([
                'chantier_id' => $chantier_id,
                'nom' => $nom,
                'description' => $description,
                'budget_alloue' => $budget_alloue,
                'parent_id' => $parent_id ?: null
            ]);

            setFlashMessage('Poste budgétaire créé avec succès', 'success');
            redirect("chantier_view.php?id=$chantier_id");
        } catch (PDOException $e) {
            $errors[] = 'Erreur lors de la création du poste';
        }
    }
}

include 'includes/header.php';
?>

<div class="mb-2">
    <a href="chantier_view.php?id=<?php echo $chantier_id; ?>">&larr; Retour au chantier</a>
</div>

<div class="card">
    <div class="card-header">
        <h2><?php echo $parent_poste ? 'Ajouter une sous-catégorie' : 'Ajouter un poste budgétaire'; ?></h2>
    </div>
    <div class="card-body">
        <?php if ($parent_poste): ?>
            <div class="alert alert-info">
                Sous-catégorie de: <strong><?php echo cleanOutput($parent_poste['nom']); ?></strong>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo cleanOutput($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="poste_add.php?chantier_id=<?php echo $chantier_id; ?><?php echo $parent_id ? '&parent_id=' . $parent_id : ''; ?>">
            <div class="form-group">
                <label for="nom" class="form-label">Nom du poste *</label>
                <input type="text" id="nom" name="nom" class="form-control" required
                       value="<?php echo cleanOutput($_POST['nom'] ?? ''); ?>"
                       placeholder="Ex: Gros œuvre, Électricité, Plomberie...">
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" class="form-control"><?php echo cleanOutput($_POST['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="budget_alloue" class="form-label">Budget alloué (€)</label>
                <input type="number" id="budget_alloue" name="budget_alloue" class="form-control"
                       step="0.01" min="0" value="<?php echo cleanOutput($_POST['budget_alloue'] ?? '0'); ?>">
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-success">Créer le poste</button>
                <a href="chantier_view.php?id=<?php echo $chantier_id; ?>" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
