<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

$page_title = 'Ajouter un versement';
$db = Database::getInstance()->getConnection();
$errors = [];
$financement_id = $_GET['financement_id'] ?? 0;

// Récupérer le financement
$stmt = $db->prepare("
    SELECT cf.*, f.nom as financeur_nom, c.nom as chantier_nom, c.user_id
    FROM chantier_financements cf
    LEFT JOIN financeurs f ON cf.financeur_id = f.id
    LEFT JOIN chantiers c ON cf.chantier_id = c.id
    WHERE cf.id = :id
");
$stmt->execute(['id' => $financement_id]);
$financement = $stmt->fetch();

if (!$financement) {
    setFlashMessage('Financement introuvable', 'danger');
    redirect('chantiers.php');
}

if (!isAdmin() && $financement['user_id'] != $_SESSION['user_id']) {
    setFlashMessage('Vous n\'avez pas les droits', 'danger');
    redirect('chantiers.php');
}

$restant = $financement['montant_prevu'] - $financement['montant_verse'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $montant = $_POST['montant'] ?? 0;
    $date_versement = $_POST['date_versement'] ?? '';
    $reference = trim($_POST['reference'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if ($montant <= 0) $errors[] = 'Le montant doit être supérieur à 0';
    if (empty($date_versement)) $errors[] = 'La date est obligatoire';

    if (empty($errors)) {
        try {
            $stmt = $db->prepare("
                INSERT INTO versements (financement_id, montant, date_versement, reference, notes)
                VALUES (:financement_id, :montant, :date_versement, :reference, :notes)
            ");

            $stmt->execute([
                'financement_id' => $financement_id,
                'montant' => $montant,
                'date_versement' => $date_versement,
                'reference' => $reference ?: null,
                'notes' => $notes ?: null
            ]);

            setFlashMessage('Versement enregistré avec succès', 'success');
            redirect("financement_view.php?id=$financement_id");
        } catch (PDOException $e) {
            $errors[] = 'Erreur lors de l\'enregistrement du versement';
        }
    }
}

include 'includes/header.php';
?>

<div class="mb-2">
    <a href="financement_view.php?id=<?php echo $financement_id; ?>">&larr; Retour au financement</a>
</div>

<div class="card">
    <div class="card-header">
        <h2>Ajouter un versement</h2>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <strong>Financeur:</strong> <?php echo cleanOutput($financement['financeur_nom']); ?><br>
            <strong>Chantier:</strong> <?php echo cleanOutput($financement['chantier_nom']); ?><br>
            <strong>Montant prévu:</strong> <?php echo formatMontant($financement['montant_prevu']); ?><br>
            <strong>Déjà versé:</strong> <?php echo formatMontant($financement['montant_verse']); ?><br>
            <strong>Restant:</strong> <?php echo formatMontant($restant); ?>
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

        <form method="POST" action="versement_add.php?financement_id=<?php echo $financement_id; ?>">
            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label for="montant" class="form-label">Montant versé (€) *</label>
                        <input type="number" id="montant" name="montant" class="form-control" required
                               step="0.01" min="0.01" value="<?php echo cleanOutput($_POST['montant'] ?? $restant); ?>">
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label for="date_versement" class="form-label">Date du versement *</label>
                        <input type="date" id="date_versement" name="date_versement" class="form-control" required
                               value="<?php echo cleanOutput($_POST['date_versement'] ?? date('Y-m-d')); ?>">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="reference" class="form-label">Référence</label>
                <input type="text" id="reference" name="reference" class="form-control"
                       placeholder="Numéro de virement, chèque..."
                       value="<?php echo cleanOutput($_POST['reference'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="notes" class="form-label">Notes</label>
                <textarea id="notes" name="notes" class="form-control"><?php echo cleanOutput($_POST['notes'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-success">Enregistrer le versement</button>
                <a href="financement_view.php?id=<?php echo $financement_id; ?>" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
