<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

$page_title = 'Nouveau chantier';
$db = Database::getInstance()->getConnection();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $date_debut = $_POST['date_debut'] ?? '';
    $date_fin_prevue = $_POST['date_fin_prevue'] ?? '';
    $budget_total = $_POST['budget_total'] ?? 0;
    $statut = $_POST['statut'] ?? 'planification';
    $user_id = $_SESSION['user_id'];

    // Validation
    if (empty($nom)) $errors[] = 'Le nom du chantier est obligatoire';
    if (empty($adresse)) $errors[] = 'L\'adresse est obligatoire';
    if (empty($date_debut)) $errors[] = 'La date de début est obligatoire';

    if (empty($errors)) {
        try {
            $stmt = $db->prepare("
                INSERT INTO chantiers (nom, description, adresse, date_debut, date_fin_prevue, budget_total, statut, user_id)
                VALUES (:nom, :description, :adresse, :date_debut, :date_fin_prevue, :budget_total, :statut, :user_id)
            ");

            $stmt->execute([
                'nom' => $nom,
                'description' => $description,
                'adresse' => $adresse,
                'date_debut' => $date_debut,
                'date_fin_prevue' => $date_fin_prevue ?: null,
                'budget_total' => $budget_total,
                'statut' => $statut,
                'user_id' => $user_id
            ]);

            $chantier_id = $db->lastInsertId();
            setFlashMessage('Chantier créé avec succès', 'success');
            redirect("chantier_view.php?id=$chantier_id");
        } catch (PDOException $e) {
            $errors[] = 'Erreur lors de la création du chantier';
        }
    }
}

include 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2>Nouveau chantier</h2>
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

        <form method="POST" action="chantier_add.php">
            <div class="row">
                <div class="col-8">
                    <div class="form-group">
                        <label for="nom" class="form-label">Nom du chantier *</label>
                        <input type="text" id="nom" name="nom" class="form-control" required
                               value="<?php echo cleanOutput($_POST['nom'] ?? ''); ?>">
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label for="statut" class="form-label">Statut</label>
                        <select id="statut" name="statut" class="form-control">
                            <option value="planification">Planification</option>
                            <option value="en_cours">En cours</option>
                            <option value="suspendu">Suspendu</option>
                            <option value="termine">Terminé</option>
                            <option value="annule">Annulé</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" class="form-control"><?php echo cleanOutput($_POST['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="adresse" class="form-label">Adresse *</label>
                <input type="text" id="adresse" name="adresse" class="form-control" required
                       value="<?php echo cleanOutput($_POST['adresse'] ?? ''); ?>">
            </div>

            <div class="row">
                <div class="col-4">
                    <div class="form-group">
                        <label for="date_debut" class="form-label">Date de début *</label>
                        <input type="date" id="date_debut" name="date_debut" class="form-control" required
                               value="<?php echo cleanOutput($_POST['date_debut'] ?? ''); ?>">
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label for="date_fin_prevue" class="form-label">Date de fin prévue</label>
                        <input type="date" id="date_fin_prevue" name="date_fin_prevue" class="form-control"
                               value="<?php echo cleanOutput($_POST['date_fin_prevue'] ?? ''); ?>">
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label for="budget_total" class="form-label">Budget total (€)</label>
                        <input type="number" id="budget_total" name="budget_total" class="form-control"
                               step="0.01" min="0" value="<?php echo cleanOutput($_POST['budget_total'] ?? '0'); ?>">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-success">Créer le chantier</button>
                <a href="chantiers.php" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
