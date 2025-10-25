<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

$db = Database::getInstance()->getConnection();
$poste_id = $_GET['id'] ?? 0;

// Récupérer le poste
$stmt = $db->prepare("
    SELECT pb.*, c.nom as chantier_nom, c.id as chantier_id
    FROM postes_budgetaires pb
    JOIN chantiers c ON pb.chantier_id = c.id
    WHERE pb.id = :id
");
$stmt->execute(['id' => $poste_id]);
$poste = $stmt->fetch();

if (!$poste) {
    setFlashMessage('Poste introuvable', 'danger');
    redirect('chantiers.php');
}

// Vérifier les droits
$stmt = $db->prepare("SELECT user_id FROM chantiers WHERE id = :id");
$stmt->execute(['id' => $poste['chantier_id']]);
$chantier = $stmt->fetch();

if (!isAdmin() && $chantier['user_id'] != $_SESSION['user_id']) {
    setFlashMessage('Vous n\'avez pas accès à ce poste', 'danger');
    redirect('chantiers.php');
}

$page_title = $poste['nom'];

// Récupérer les sous-catégories
$stmt = $db->prepare("
    SELECT * FROM postes_budgetaires
    WHERE parent_id = :parent_id
    ORDER BY ordre, nom
");
$stmt->execute(['parent_id' => $poste_id]);
$sous_postes = $stmt->fetchAll();

// Récupérer les dépenses
$stmt = $db->prepare("
    SELECT * FROM depenses
    WHERE poste_id = :poste_id
    ORDER BY date_depense DESC
");
$stmt->execute(['poste_id' => $poste_id]);
$depenses = $stmt->fetchAll();

// Recalculer le budget consommé
$total_depenses = 0;
foreach ($depenses as $depense) {
    if ($depense['statut'] !== 'prevue') {
        $total_depenses += $depense['montant'];
    }
}

// Mettre à jour le budget consommé si nécessaire
if ($total_depenses != $poste['budget_consomme']) {
    $stmt = $db->prepare("UPDATE postes_budgetaires SET budget_consomme = :montant WHERE id = :id");
    $stmt->execute(['montant' => $total_depenses, 'id' => $poste_id]);
    $poste['budget_consomme'] = $total_depenses;
}

$restant = $poste['budget_alloue'] - $poste['budget_consomme'];
$pourcentage = calculatePercentage($poste['budget_consomme'], $poste['budget_alloue']);

include 'includes/header.php';
?>

<div class="mb-2">
    <a href="chantier_view.php?id=<?php echo $poste['chantier_id']; ?>">&larr; Retour au chantier</a>
</div>

<!-- Informations du poste -->
<div class="card mb-3">
    <div class="card-header">
        <h2><?php echo cleanOutput($poste['nom']); ?></h2>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-6">
                <p><strong>Chantier:</strong> <?php echo cleanOutput($poste['chantier_nom']); ?></p>
                <?php if ($poste['description']): ?>
                    <p><strong>Description:</strong><br><?php echo nl2br(cleanOutput($poste['description'])); ?></p>
                <?php endif; ?>
            </div>
            <div class="col-6">
                <p><strong>Budget alloué:</strong> <?php echo formatMontant($poste['budget_alloue']); ?></p>
                <p><strong>Budget consommé:</strong> <?php echo formatMontant($poste['budget_consomme']); ?></p>
                <p><strong>Restant:</strong>
                    <span style="color: <?php echo $restant < 0 ? '#e74c3c' : '#27ae60'; ?>; font-weight: bold;">
                        <?php echo formatMontant($restant); ?>
                    </span>
                </p>
            </div>
        </div>

        <div class="progress" style="height: 30px;">
            <div class="progress-bar <?php echo $pourcentage >= 90 ? 'progress-bar-danger' : ($pourcentage >= 75 ? 'progress-bar-warning' : ''); ?>"
                 style="width: <?php echo min($pourcentage, 100); ?>%">
                <?php echo $pourcentage; ?>%
            </div>
        </div>
    </div>
</div>

<!-- Sous-catégories -->
<div class="card mb-3">
    <div class="card-header">
        <span>Sous-catégories</span>
        <a href="poste_add.php?chantier_id=<?php echo $poste['chantier_id']; ?>&parent_id=<?php echo $poste_id; ?>"
           class="btn btn-primary btn-sm">Ajouter une sous-catégorie</a>
    </div>
    <div class="card-body">
        <?php if (empty($sous_postes)): ?>
            <p class="text-muted">Aucune sous-catégorie définie.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Budget Alloué</th>
                        <th>Budget Consommé</th>
                        <th>Restant</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sous_postes as $sous_poste):
                        $sous_restant = $sous_poste['budget_alloue'] - $sous_poste['budget_consomme'];
                    ?>
                        <tr>
                            <td><?php echo cleanOutput($sous_poste['nom']); ?></td>
                            <td><?php echo formatMontant($sous_poste['budget_alloue']); ?></td>
                            <td><?php echo formatMontant($sous_poste['budget_consomme']); ?></td>
                            <td><?php echo formatMontant($sous_restant); ?></td>
                            <td>
                                <a href="poste_view.php?id=<?php echo $sous_poste['id']; ?>" class="btn btn-sm btn-primary">Voir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Dépenses -->
<div class="card mb-3">
    <div class="card-header">
        <span>Dépenses</span>
        <a href="depense_add.php?poste_id=<?php echo $poste_id; ?>" class="btn btn-primary btn-sm">Ajouter une dépense</a>
    </div>
    <div class="card-body">
        <?php if (empty($depenses)): ?>
            <p class="text-muted">Aucune dépense enregistrée.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Fournisseur</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($depenses as $depense): ?>
                        <tr>
                            <td><?php echo formatDate($depense['date_depense']); ?></td>
                            <td><?php echo cleanOutput($depense['description']); ?></td>
                            <td><?php echo cleanOutput($depense['fournisseur']); ?></td>
                            <td><?php echo formatMontant($depense['montant']); ?></td>
                            <td>
                                <span class="badge <?php echo getStatusBadgeClass($depense['statut']); ?>">
                                    <?php echo translateStatus($depense['statut']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="depense_edit.php?id=<?php echo $depense['id']; ?>" class="btn btn-sm btn-secondary">Modifier</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
