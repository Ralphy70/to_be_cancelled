<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

$db = Database::getInstance()->getConnection();
$chantier_id = $_GET['id'] ?? 0;

// Récupérer le chantier
$stmt = $db->prepare("
    SELECT c.*, u.username
    FROM chantiers c
    LEFT JOIN users u ON c.user_id = u.id
    WHERE c.id = :id
");
$stmt->execute(['id' => $chantier_id]);
$chantier = $stmt->fetch();

if (!$chantier) {
    setFlashMessage('Chantier introuvable', 'danger');
    redirect('chantiers.php');
}

// Vérifier les droits
if (!isAdmin() && $chantier['user_id'] != $_SESSION['user_id']) {
    setFlashMessage('Vous n\'avez pas accès à ce chantier', 'danger');
    redirect('chantiers.php');
}

$page_title = $chantier['nom'];

// Récupérer les postes budgétaires principaux
$stmt = $db->prepare("
    SELECT * FROM postes_budgetaires
    WHERE chantier_id = :chantier_id AND parent_id IS NULL
    ORDER BY ordre, nom
");
$stmt->execute(['chantier_id' => $chantier_id]);
$postes = $stmt->fetchAll();

// Calculer le total alloué et consommé
$total_alloue = 0;
$total_consomme = 0;
foreach ($postes as $poste) {
    $total_alloue += $poste['budget_alloue'];
    $total_consomme += $poste['budget_consomme'];
}

// Récupérer les financements
$stmt = $db->prepare("
    SELECT cf.*, f.nom as financeur_nom, f.type as financeur_type
    FROM chantier_financements cf
    LEFT JOIN financeurs f ON cf.financeur_id = f.id
    WHERE cf.chantier_id = :chantier_id
    ORDER BY cf.created_at DESC
");
$stmt->execute(['chantier_id' => $chantier_id]);
$financements = $stmt->fetchAll();

// Calculer le total prévu et versé
$total_prevu = 0;
$total_verse = 0;
foreach ($financements as $financement) {
    $total_prevu += $financement['montant_prevu'];
    $total_verse += $financement['montant_verse'];
}

include 'includes/header.php';
?>

<div class="mb-2">
    <a href="chantiers.php">&larr; Retour aux chantiers</a>
</div>

<!-- Informations du chantier -->
<div class="card mb-3">
    <div class="card-header">
        <h2><?php echo cleanOutput($chantier['nom']); ?></h2>
        <div>
            <a href="chantier_edit.php?id=<?php echo $chantier_id; ?>" class="btn btn-secondary btn-sm">Modifier</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-6">
                <p><strong>Description:</strong><br><?php echo nl2br(cleanOutput($chantier['description'])); ?></p>
                <p><strong>Adresse:</strong> <?php echo cleanOutput($chantier['adresse']); ?></p>
                <p><strong>Responsable:</strong> <?php echo cleanOutput($chantier['username']); ?></p>
            </div>
            <div class="col-6">
                <p><strong>Statut:</strong>
                    <span class="badge <?php echo getStatusBadgeClass($chantier['statut']); ?>">
                        <?php echo translateStatus($chantier['statut']); ?>
                    </span>
                </p>
                <p><strong>Date de début:</strong> <?php echo formatDate($chantier['date_debut']); ?></p>
                <p><strong>Date de fin prévue:</strong> <?php echo formatDate($chantier['date_fin_prevue']); ?></p>
                <p><strong>Budget total:</strong> <?php echo formatMontant($chantier['budget_total']); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Statistiques financières -->
<div class="row mb-3">
    <div class="col-3">
        <div class="stat-card">
            <div class="stat-card-value"><?php echo formatMontant($total_prevu); ?></div>
            <div class="stat-card-label">Financement Prévu</div>
        </div>
    </div>
    <div class="col-3">
        <div class="stat-card">
            <div class="stat-card-value"><?php echo formatMontant($total_verse); ?></div>
            <div class="stat-card-label">Fonds Versés</div>
        </div>
    </div>
    <div class="col-3">
        <div class="stat-card">
            <div class="stat-card-value"><?php echo formatMontant($total_alloue); ?></div>
            <div class="stat-card-label">Budget Alloué</div>
        </div>
    </div>
    <div class="col-3">
        <div class="stat-card">
            <div class="stat-card-value"><?php echo formatMontant($total_consomme); ?></div>
            <div class="stat-card-label">Budget Consommé</div>
        </div>
    </div>
</div>

<!-- Postes budgétaires -->
<div class="card mb-3">
    <div class="card-header">
        <span>Postes budgétaires</span>
        <a href="poste_add.php?chantier_id=<?php echo $chantier_id; ?>" class="btn btn-primary btn-sm">Ajouter un poste</a>
    </div>
    <div class="card-body">
        <?php if (empty($postes)): ?>
            <p class="text-muted">Aucun poste budgétaire défini.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Budget Alloué</th>
                        <th>Budget Consommé</th>
                        <th>Restant</th>
                        <th>Progression</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($postes as $poste):
                        $restant = $poste['budget_alloue'] - $poste['budget_consomme'];
                        $pourcentage = calculatePercentage($poste['budget_consomme'], $poste['budget_alloue']);
                        $progress_class = $pourcentage >= 90 ? 'progress-bar-danger' : ($pourcentage >= 75 ? 'progress-bar-warning' : '');
                    ?>
                        <tr>
                            <td><strong><?php echo cleanOutput($poste['nom']); ?></strong></td>
                            <td><?php echo formatMontant($poste['budget_alloue']); ?></td>
                            <td><?php echo formatMontant($poste['budget_consomme']); ?></td>
                            <td><?php echo formatMontant($restant); ?></td>
                            <td>
                                <div class="progress">
                                    <div class="progress-bar <?php echo $progress_class; ?>"
                                         style="width: <?php echo min($pourcentage, 100); ?>%">
                                        <?php echo $pourcentage; ?>%
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="poste_view.php?id=<?php echo $poste['id']; ?>" class="btn btn-sm btn-primary">Détails</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Financements -->
<div class="card mb-3">
    <div class="card-header">
        <span>Financements</span>
        <a href="financement_add.php?chantier_id=<?php echo $chantier_id; ?>" class="btn btn-primary btn-sm">Ajouter un financement</a>
    </div>
    <div class="card-body">
        <?php if (empty($financements)): ?>
            <p class="text-muted">Aucun financement enregistré.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Financeur</th>
                        <th>Type</th>
                        <th>Montant Prévu</th>
                        <th>Montant Versé</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($financements as $financement):
                        $pourcentage = calculatePercentage($financement['montant_verse'], $financement['montant_prevu']);
                    ?>
                        <tr>
                            <td><strong><?php echo cleanOutput($financement['financeur_nom']); ?></strong></td>
                            <td><?php echo cleanOutput($financement['financeur_type']); ?></td>
                            <td><?php echo formatMontant($financement['montant_prevu']); ?></td>
                            <td>
                                <?php echo formatMontant($financement['montant_verse']); ?>
                                <small class="text-muted">(<?php echo $pourcentage; ?>%)</small>
                            </td>
                            <td>
                                <span class="badge <?php echo getStatusBadgeClass($financement['statut']); ?>">
                                    <?php echo translateStatus($financement['statut']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="financement_view.php?id=<?php echo $financement['id']; ?>" class="btn btn-sm btn-primary">Détails</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
