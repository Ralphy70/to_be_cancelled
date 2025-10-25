<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

$db = Database::getInstance()->getConnection();
$financement_id = $_GET['id'] ?? 0;

// Récupérer le financement
$stmt = $db->prepare("
    SELECT cf.*, f.nom as financeur_nom, f.type as financeur_type,
           c.nom as chantier_nom, c.id as chantier_id, c.user_id
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

// Vérifier les droits
if (!isAdmin() && $financement['user_id'] != $_SESSION['user_id']) {
    setFlashMessage('Vous n\'avez pas accès à ce financement', 'danger');
    redirect('chantiers.php');
}

$page_title = 'Financement - ' . $financement['financeur_nom'];

// Récupérer les versements
$stmt = $db->prepare("
    SELECT * FROM versements
    WHERE financement_id = :financement_id
    ORDER BY date_versement DESC
");
$stmt->execute(['financement_id' => $financement_id]);
$versements = $stmt->fetchAll();

// Calculer le total versé
$total_verse = 0;
foreach ($versements as $versement) {
    $total_verse += $versement['montant'];
}

// Mettre à jour le montant versé si nécessaire
if ($total_verse != $financement['montant_verse']) {
    $stmt = $db->prepare("UPDATE chantier_financements SET montant_verse = :montant WHERE id = :id");
    $stmt->execute(['montant' => $total_verse, 'id' => $financement_id]);
    $financement['montant_verse'] = $total_verse;
}

$pourcentage = calculatePercentage($financement['montant_verse'], $financement['montant_prevu']);
$restant = $financement['montant_prevu'] - $financement['montant_verse'];

include 'includes/header.php';
?>

<div class="mb-2">
    <a href="chantier_view.php?id=<?php echo $financement['chantier_id']; ?>">&larr; Retour au chantier</a>
</div>

<!-- Informations du financement -->
<div class="card mb-3">
    <div class="card-header">
        <h2>Financement - <?php echo cleanOutput($financement['financeur_nom']); ?></h2>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-6">
                <p><strong>Chantier:</strong> <?php echo cleanOutput($financement['chantier_nom']); ?></p>
                <p><strong>Financeur:</strong> <?php echo cleanOutput($financement['financeur_nom']); ?></p>
                <p><strong>Type:</strong> <?php echo cleanOutput($financement['financeur_type']); ?></p>
                <p><strong>Statut:</strong>
                    <span class="badge <?php echo getStatusBadgeClass($financement['statut']); ?>">
                        <?php echo translateStatus($financement['statut']); ?>
                    </span>
                </p>
            </div>
            <div class="col-6">
                <p><strong>Montant prévu:</strong> <?php echo formatMontant($financement['montant_prevu']); ?></p>
                <p><strong>Montant versé:</strong> <?php echo formatMontant($financement['montant_verse']); ?></p>
                <p><strong>Restant à verser:</strong>
                    <span style="color: <?php echo $restant > 0 ? '#f39c12' : '#27ae60'; ?>; font-weight: bold;">
                        <?php echo formatMontant($restant); ?>
                    </span>
                </p>
                <?php if ($financement['date_accord']): ?>
                    <p><strong>Date d'accord:</strong> <?php echo formatDate($financement['date_accord']); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($financement['conditions']): ?>
            <p><strong>Conditions:</strong><br><?php echo nl2br(cleanOutput($financement['conditions'])); ?></p>
        <?php endif; ?>

        <div class="progress" style="height: 30px; margin-top: 1rem;">
            <div class="progress-bar <?php echo $pourcentage >= 100 ? '' : 'progress-bar-warning'; ?>"
                 style="width: <?php echo min($pourcentage, 100); ?>%">
                <?php echo $pourcentage; ?>% versé
            </div>
        </div>
    </div>
</div>

<!-- Versements -->
<div class="card mb-3">
    <div class="card-header">
        <span>Historique des versements</span>
        <a href="versement_add.php?financement_id=<?php echo $financement_id; ?>" class="btn btn-primary btn-sm">Ajouter un versement</a>
    </div>
    <div class="card-body">
        <?php if (empty($versements)): ?>
            <p class="text-muted">Aucun versement enregistré.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Montant</th>
                        <th>Référence</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($versements as $versement): ?>
                        <tr>
                            <td><?php echo formatDate($versement['date_versement']); ?></td>
                            <td><strong><?php echo formatMontant($versement['montant']); ?></strong></td>
                            <td><?php echo cleanOutput($versement['reference']); ?></td>
                            <td><?php echo cleanOutput($versement['notes']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th>Total versé:</th>
                        <th><strong><?php echo formatMontant($total_verse); ?></strong></th>
                        <th colspan="2"></th>
                    </tr>
                </tfoot>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
