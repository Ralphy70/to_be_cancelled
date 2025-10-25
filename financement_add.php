<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

$page_title = 'Ajouter un financement';
$db = Database::getInstance()->getConnection();
$errors = [];
$chantier_id = $_GET['chantier_id'] ?? 0;

// Vérifier que le chantier existe
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

// Récupérer la liste des financeurs
$stmt = $db->query("SELECT * FROM financeurs ORDER BY nom");
$financeurs = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $financeur_id = $_POST['financeur_id'] ?? 0;
    $montant_prevu = $_POST['montant_prevu'] ?? 0;
    $montant_verse = $_POST['montant_verse'] ?? 0;
    $pourcentage_participation = $_POST['pourcentage_participation'] ?? null;
    $date_accord = $_POST['date_accord'] ?? null;
    $conditions = trim($_POST['conditions'] ?? '');
    $statut = $_POST['statut'] ?? 'en_negociation';

    if ($financeur_id <= 0) $errors[] = 'Veuillez sélectionner un financeur';
    if ($montant_prevu <= 0) $errors[] = 'Le montant prévu doit être supérieur à 0';

    if (empty($errors)) {
        try {
            $stmt = $db->prepare("
                INSERT INTO chantier_financements (chantier_id, financeur_id, montant_prevu, montant_verse,
                                                    pourcentage_participation, date_accord, conditions, statut)
                VALUES (:chantier_id, :financeur_id, :montant_prevu, :montant_verse,
                        :pourcentage_participation, :date_accord, :conditions, :statut)
            ");

            $stmt->execute([
                'chantier_id' => $chantier_id,
                'financeur_id' => $financeur_id,
                'montant_prevu' => $montant_prevu,
                'montant_verse' => $montant_verse,
                'pourcentage_participation' => $pourcentage_participation ?: null,
                'date_accord' => $date_accord ?: null,
                'conditions' => $conditions ?: null,
                'statut' => $statut
            ]);

            setFlashMessage('Financement créé avec succès', 'success');
            redirect("chantier_view.php?id=$chantier_id");
        } catch (PDOException $e) {
            $errors[] = 'Erreur lors de la création du financement';
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
        <h2>Ajouter un financement</h2>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            Chantier: <strong><?php echo cleanOutput($chantier['nom']); ?></strong><br>
            Budget total: <strong><?php echo formatMontant($chantier['budget_total']); ?></strong>
        </div>

        <?php if (empty($financeurs)): ?>
            <div class="alert alert-warning">
                Aucun financeur disponible. <a href="financeur_add.php">Créez d'abord un financeur</a>.
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

        <form method="POST" action="financement_add.php?chantier_id=<?php echo $chantier_id; ?>">
            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label for="financeur_id" class="form-label">Financeur *</label>
                        <select id="financeur_id" name="financeur_id" class="form-control" required>
                            <option value="">Sélectionnez un financeur</option>
                            <?php foreach ($financeurs as $financeur): ?>
                                <option value="<?php echo $financeur['id']; ?>">
                                    <?php echo cleanOutput($financeur['nom']); ?> (<?php echo cleanOutput($financeur['type']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label for="statut" class="form-label">Statut</label>
                        <select id="statut" name="statut" class="form-control">
                            <option value="en_negociation">En négociation</option>
                            <option value="accorde" selected>Accordé</option>
                            <option value="verse_partiel">Versé partiellement</option>
                            <option value="verse_total">Versé totalement</option>
                            <option value="refuse">Refusé</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-4">
                    <div class="form-group">
                        <label for="montant_prevu" class="form-label">Montant prévu (€) *</label>
                        <input type="number" id="montant_prevu" name="montant_prevu" class="form-control" required
                               step="0.01" min="0.01" value="<?php echo cleanOutput($_POST['montant_prevu'] ?? ''); ?>">
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label for="montant_verse" class="form-label">Montant déjà versé (€)</label>
                        <input type="number" id="montant_verse" name="montant_verse" class="form-control"
                               step="0.01" min="0" value="<?php echo cleanOutput($_POST['montant_verse'] ?? '0'); ?>">
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label for="pourcentage_participation" class="form-label">% de participation</label>
                        <input type="number" id="pourcentage_participation" name="pourcentage_participation" class="form-control"
                               step="0.01" min="0" max="100" value="<?php echo cleanOutput($_POST['pourcentage_participation'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="date_accord" class="form-label">Date d'accord</label>
                <input type="date" id="date_accord" name="date_accord" class="form-control"
                       value="<?php echo cleanOutput($_POST['date_accord'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="conditions" class="form-label">Conditions</label>
                <textarea id="conditions" name="conditions" class="form-control"
                          placeholder="Décrivez les conditions du financement..."><?php echo cleanOutput($_POST['conditions'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-success">Créer le financement</button>
                <a href="chantier_view.php?id=<?php echo $chantier_id; ?>" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
