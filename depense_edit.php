<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

$page_title = 'Modifier une dépense';
$db = Database::getInstance()->getConnection();
$errors = [];
$depense_id = $_GET['id'] ?? 0;

// Récupérer la dépense
$stmt = $db->prepare("
    SELECT d.*, pb.id as poste_id, pb.nom as poste_nom, c.user_id
    FROM depenses d
    JOIN postes_budgetaires pb ON d.poste_id = pb.id
    JOIN chantiers c ON pb.chantier_id = c.id
    WHERE d.id = :id
");
$stmt->execute(['id' => $depense_id]);
$depense = $stmt->fetch();

if (!$depense) {
    setFlashMessage('Dépense introuvable', 'danger');
    redirect('chantiers.php');
}

if (!isAdmin() && $depense['user_id'] != $_SESSION['user_id']) {
    setFlashMessage('Vous n\'avez pas les droits', 'danger');
    redirect('chantiers.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = trim($_POST['description'] ?? '');
    $montant = $_POST['montant'] ?? 0;
    $date_depense = $_POST['date_depense'] ?? '';
    $fournisseur = trim($_POST['fournisseur'] ?? '');
    $numero_facture = trim($_POST['numero_facture'] ?? '');
    $statut = $_POST['statut'] ?? 'prevue';

    if (empty($description)) $errors[] = 'La description est obligatoire';
    if (empty($date_depense)) $errors[] = 'La date est obligatoire';
    if ($montant <= 0) $errors[] = 'Le montant doit être supérieur à 0';

    if (empty($errors)) {
        try {
            $stmt = $db->prepare("
                UPDATE depenses
                SET description = :description, montant = :montant, date_depense = :date_depense,
                    fournisseur = :fournisseur, numero_facture = :numero_facture, statut = :statut
                WHERE id = :id
            ");

            $stmt->execute([
                'description' => $description,
                'montant' => $montant,
                'date_depense' => $date_depense,
                'fournisseur' => $fournisseur,
                'numero_facture' => $numero_facture,
                'statut' => $statut,
                'id' => $depense_id
            ]);

            setFlashMessage('Dépense modifiée avec succès', 'success');
            redirect("poste_view.php?id=" . $depense['poste_id']);
        } catch (PDOException $e) {
            $errors[] = 'Erreur lors de la modification de la dépense';
        }
    }
} else {
    $_POST = $depense;
}

include 'includes/header.php';
?>

<div class="mb-2">
    <a href="poste_view.php?id=<?php echo $depense['poste_id']; ?>">&larr; Retour au poste</a>
</div>

<div class="card">
    <div class="card-header">
        <h2>Modifier la dépense</h2>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            Poste: <strong><?php echo cleanOutput($depense['poste_nom']); ?></strong>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo cleanOutput($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="depense_edit.php?id=<?php echo $depense_id; ?>">
            <div class="form-group">
                <label for="description" class="form-label">Description *</label>
                <input type="text" id="description" name="description" class="form-control" required
                       value="<?php echo cleanOutput($_POST['description'] ?? ''); ?>">
            </div>

            <div class="row">
                <div class="col-4">
                    <div class="form-group">
                        <label for="montant" class="form-label">Montant (€) *</label>
                        <input type="number" id="montant" name="montant" class="form-control" required
                               step="0.01" min="0.01" value="<?php echo cleanOutput($_POST['montant'] ?? ''); ?>">
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label for="date_depense" class="form-label">Date *</label>
                        <input type="date" id="date_depense" name="date_depense" class="form-control" required
                               value="<?php echo cleanOutput($_POST['date_depense'] ?? ''); ?>">
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label for="statut" class="form-label">Statut</label>
                        <select id="statut" name="statut" class="form-control">
                            <option value="prevue" <?php echo ($_POST['statut'] ?? '') == 'prevue' ? 'selected' : ''; ?>>Prévue</option>
                            <option value="engagee" <?php echo ($_POST['statut'] ?? '') == 'engagee' ? 'selected' : ''; ?>>Engagée</option>
                            <option value="payee" <?php echo ($_POST['statut'] ?? '') == 'payee' ? 'selected' : ''; ?>>Payée</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label for="fournisseur" class="form-label">Fournisseur</label>
                        <input type="text" id="fournisseur" name="fournisseur" class="form-control"
                               value="<?php echo cleanOutput($_POST['fournisseur'] ?? ''); ?>">
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label for="numero_facture" class="form-label">Numéro de facture</label>
                        <input type="text" id="numero_facture" name="numero_facture" class="form-control"
                               value="<?php echo cleanOutput($_POST['numero_facture'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-success">Enregistrer les modifications</button>
                <a href="poste_view.php?id=<?php echo $depense['poste_id']; ?>" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
