<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

$page_title = 'Ajouter une dépense';
$db = Database::getInstance()->getConnection();
$errors = [];
$poste_id = $_GET['poste_id'] ?? 0;

// Vérifier que le poste existe
$stmt = $db->prepare("
    SELECT pb.*, c.id as chantier_id, c.user_id
    FROM postes_budgetaires pb
    JOIN chantiers c ON pb.chantier_id = c.id
    WHERE pb.id = :id
");
$stmt->execute(['id' => $poste_id]);
$poste = $stmt->fetch();

if (!$poste) {
    setFlashMessage('Poste budgétaire introuvable', 'danger');
    redirect('chantiers.php');
}

if (!isAdmin() && $poste['user_id'] != $_SESSION['user_id']) {
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
                INSERT INTO depenses (poste_id, description, montant, date_depense, fournisseur, numero_facture, statut)
                VALUES (:poste_id, :description, :montant, :date_depense, :fournisseur, :numero_facture, :statut)
            ");

            $stmt->execute([
                'poste_id' => $poste_id,
                'description' => $description,
                'montant' => $montant,
                'date_depense' => $date_depense,
                'fournisseur' => $fournisseur,
                'numero_facture' => $numero_facture,
                'statut' => $statut
            ]);

            setFlashMessage('Dépense créée avec succès', 'success');
            redirect("poste_view.php?id=$poste_id");
        } catch (PDOException $e) {
            $errors[] = 'Erreur lors de la création de la dépense';
        }
    }
}

include 'includes/header.php';
?>

<div class="mb-2">
    <a href="poste_view.php?id=<?php echo $poste_id; ?>">&larr; Retour au poste</a>
</div>

<div class="card">
    <div class="card-header">
        <h2>Ajouter une dépense</h2>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            Poste: <strong><?php echo cleanOutput($poste['nom']); ?></strong><br>
            Budget restant: <strong><?php echo formatMontant($poste['budget_alloue'] - $poste['budget_consomme']); ?></strong>
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

        <form method="POST" action="depense_add.php?poste_id=<?php echo $poste_id; ?>">
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
                               value="<?php echo cleanOutput($_POST['date_depense'] ?? date('Y-m-d')); ?>">
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label for="statut" class="form-label">Statut</label>
                        <select id="statut" name="statut" class="form-control">
                            <option value="prevue">Prévue</option>
                            <option value="engagee">Engagée</option>
                            <option value="payee" selected>Payée</option>
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
                <button type="submit" class="btn btn-success">Enregistrer la dépense</button>
                <a href="poste_view.php?id=<?php echo $poste_id; ?>" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
