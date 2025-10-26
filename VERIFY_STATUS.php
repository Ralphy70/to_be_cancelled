<?php
/**
 * Script de vérification rapide du statut de l'application
 * Accédez à ce fichier via : http://votre-domaine/VERIFY_STATUS.php
 */

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification du Statut</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .card { background: white; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-top: 0; }
        h2 { color: #555; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .info { color: #17a2b8; font-weight: bold; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        ul { line-height: 1.8; }
        .status-icon { font-size: 1.2em; margin-right: 5px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: bold; }
        .highlight { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 15px 0; }
    </style>
</head>
<body>
    <h1>🔍 Vérification du Statut de l'Application</h1>

    <?php
    $host = '127.0.0.1';
    $user = 'chantiers';
    $pass = 'JhebGYv6n8nFF0lO0';
    $dbname = 'chantiers';

    $checks = [];
    $critical_error = false;
    ?>

    <!-- TEST 1: Configuration Files -->
    <div class="card">
        <h2>1. Fichiers de Configuration</h2>
        <?php
        $config_exists = file_exists('config/config.php');
        $api_config_exists = file_exists('api/config.php');
        $database_exists = file_exists('config/database.php');

        if ($config_exists && $api_config_exists && $database_exists) {
            echo '<p><span class="status-icon">✅</span> <span class="success">Tous les fichiers de configuration sont présents</span></p>';
            $checks['config_files'] = true;
        } else {
            echo '<p><span class="status-icon">❌</span> <span class="error">Fichiers de configuration manquants !</span></p>';
            $checks['config_files'] = false;
            $critical_error = true;
        }
        ?>
        <ul>
            <li><?php echo $config_exists ? '✅' : '❌'; ?> config/config.php</li>
            <li><?php echo $api_config_exists ? '✅' : '❌'; ?> api/config.php</li>
            <li><?php echo $database_exists ? '✅' : '❌'; ?> config/database.php</li>
        </ul>
    </div>

    <!-- TEST 2: Database Connection -->
    <div class="card">
        <h2>2. Connexion à la Base de Données</h2>
        <?php
        try {
            $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            echo '<p><span class="status-icon">✅</span> <span class="success">Connexion à la base de données réussie !</span></p>';
            $checks['db_connection'] = true;

            // Get MySQL version
            $version = $pdo->query('SELECT VERSION()')->fetchColumn();
            echo '<p><span class="info">Version MySQL/MariaDB :</span> ' . htmlspecialchars($version) . '</p>';

        } catch (PDOException $e) {
            echo '<p><span class="status-icon">❌</span> <span class="error">Échec de connexion à la base de données</span></p>';
            echo '<p><code>' . htmlspecialchars($e->getMessage()) . '</code></p>';
            $checks['db_connection'] = false;
            $critical_error = true;
            $pdo = null;
        }
        ?>
        <table>
            <tr>
                <th>Paramètre</th>
                <th>Valeur</th>
            </tr>
            <tr>
                <td>Hôte</td>
                <td><code><?php echo htmlspecialchars($host); ?></code></td>
            </tr>
            <tr>
                <td>Base de données</td>
                <td><code><?php echo htmlspecialchars($dbname); ?></code></td>
            </tr>
            <tr>
                <td>Utilisateur</td>
                <td><code><?php echo htmlspecialchars($user); ?></code></td>
            </tr>
            <tr>
                <td>Mot de passe</td>
                <td><code><?php echo str_repeat('*', strlen($pass)); ?></code></td>
            </tr>
        </table>
    </div>

    <!-- TEST 3: Database Tables -->
    <?php if ($pdo): ?>
    <div class="card">
        <h2>3. Structure de la Base de Données</h2>
        <?php
        try {
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $required_tables = [
                'users', 'chantiers', 'postes_budgetaires', 'depenses',
                'financeurs', 'chantier_financements', 'versements', 'documents'
            ];

            $missing_tables = array_diff($required_tables, $tables);

            if (empty($missing_tables)) {
                echo '<p><span class="status-icon">✅</span> <span class="success">Toutes les tables requises sont présentes (' . count($tables) . ' tables)</span></p>';
                $checks['db_tables'] = true;
            } else {
                echo '<p><span class="status-icon">⚠️</span> <span class="warning">Tables manquantes : ' . implode(', ', $missing_tables) . '</span></p>';
                $checks['db_tables'] = false;
            }

            echo '<table>';
            echo '<tr><th>Table</th><th>Statut</th><th>Nombre de lignes</th></tr>';

            foreach ($required_tables as $table) {
                $exists = in_array($table, $tables);
                $icon = $exists ? '✅' : '❌';
                $status = $exists ? '<span class="success">Présente</span>' : '<span class="error">Manquante</span>';

                $count = '-';
                if ($exists) {
                    try {
                        $count_stmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
                        $count = $count_stmt->fetchColumn();
                    } catch (Exception $e) {
                        $count = '<span class="error">Erreur</span>';
                    }
                }

                echo "<tr><td><code>$table</code></td><td>$icon $status</td><td>$count</td></tr>";
            }
            echo '</table>';

        } catch (PDOException $e) {
            echo '<p><span class="status-icon">❌</span> <span class="error">Erreur lors de la vérification des tables</span></p>';
            echo '<p><code>' . htmlspecialchars($e->getMessage()) . '</code></p>';
            $checks['db_tables'] = false;
        }
        ?>
    </div>

    <!-- TEST 4: Admin User -->
    <div class="card">
        <h2>4. Utilisateur Administrateur</h2>
        <?php
        try {
            $stmt = $pdo->query("SELECT id, username, email, role FROM users WHERE role = 'admin' LIMIT 1");
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin) {
                echo '<p><span class="status-icon">✅</span> <span class="success">Utilisateur admin trouvé</span></p>';
                $checks['admin_user'] = true;

                echo '<table>';
                echo '<tr><th>Champ</th><th>Valeur</th></tr>';
                echo '<tr><td>ID</td><td>' . htmlspecialchars($admin['id']) . '</td></tr>';
                echo '<tr><td>Username</td><td><code>' . htmlspecialchars($admin['username']) . '</code></td></tr>';
                echo '<tr><td>Email</td><td>' . htmlspecialchars($admin['email']) . '</td></tr>';
                echo '<tr><td>Rôle</td><td>' . htmlspecialchars($admin['role']) . '</td></tr>';
                echo '</table>';

                echo '<div class="highlight">';
                echo '<strong>🔑 Identifiants de connexion par défaut :</strong><br>';
                echo 'Username : <code>admin</code><br>';
                echo 'Password : <code>admin123</code>';
                echo '</div>';
            } else {
                echo '<p><span class="status-icon">❌</span> <span class="error">Aucun utilisateur admin trouvé</span></p>';
                echo '<p>Exécutez : <code>bash clean_repair.sh</code></p>';
                $checks['admin_user'] = false;
            }

        } catch (PDOException $e) {
            echo '<p><span class="status-icon">❌</span> <span class="error">Erreur lors de la vérification de l\'utilisateur admin</span></p>';
            echo '<p><code>' . htmlspecialchars($e->getMessage()) . '</code></p>';
            $checks['admin_user'] = false;
        }
        ?>
    </div>

    <!-- TEST 5: API Configuration -->
    <div class="card">
        <h2>5. Configuration de l'API REST</h2>
        <?php
        $api_files = [
            'api/config.php' => 'Configuration',
            'api/auth.php' => 'Authentification',
            'api/chantiers.php' => 'Chantiers',
            'api/depenses.php' => 'Dépenses',
            'api/financeurs.php' => 'Financeurs',
            'api/versements.php' => 'Versements'
        ];

        $all_api_files_exist = true;
        echo '<ul>';
        foreach ($api_files as $file => $label) {
            $exists = file_exists($file);
            $icon = $exists ? '✅' : '❌';
            echo "<li>$icon <code>$file</code> - $label</li>";
            if (!$exists) $all_api_files_exist = false;
        }
        echo '</ul>';

        if ($all_api_files_exist) {
            echo '<p><span class="status-icon">✅</span> <span class="success">Tous les fichiers API sont présents</span></p>';
            $checks['api_files'] = true;
        } else {
            echo '<p><span class="status-icon">⚠️</span> <span class="warning">Certains fichiers API sont manquants</span></p>';
            $checks['api_files'] = false;
        }
        ?>
    </div>
    <?php endif; ?>

    <!-- SUMMARY -->
    <div class="card">
        <h2>📊 Résumé</h2>
        <?php
        $total_checks = count($checks);
        $passed_checks = count(array_filter($checks));
        $percentage = $total_checks > 0 ? round(($passed_checks / $total_checks) * 100) : 0;

        echo "<p><strong>Tests réussis :</strong> $passed_checks / $total_checks ($percentage%)</p>";

        if ($critical_error) {
            echo '<div class="highlight">';
            echo '<h3>⚠️ Action Requise</h3>';
            echo '<p>Des erreurs critiques ont été détectées. Suivez ces étapes :</p>';
            echo '<ol>';
            echo '<li>Assurez-vous d\'avoir fait <code>git pull</code> pour récupérer les derniers fichiers</li>';
            echo '<li>Si la base de données a des problèmes, exécutez : <code>bash clean_repair.sh</code></li>';
            echo '<li>Consultez le guide : <a href="DEPLOYMENT_GUIDE.md" target="_blank">DEPLOYMENT_GUIDE.md</a></li>';
            echo '</ol>';
            echo '</div>';
        } elseif ($percentage === 100) {
            echo '<div style="background: #d4edda; border-left: 4px solid #28a745; padding: 15px; margin: 15px 0;">';
            echo '<h3>✅ Tout est prêt !</h3>';
            echo '<p>Votre application est correctement configurée et prête à être utilisée.</p>';
            echo '<p><strong>Prochaines étapes :</strong></p>';
            echo '<ul>';
            echo '<li>Testez la connexion : <a href="login.php">login.php</a> (admin / admin123)</li>';
            echo '<li>Supprimez les fichiers de diagnostic une fois que tout fonctionne</li>';
            echo '<li>Changez le mot de passe admin</li>';
            echo '<li>Configurez l\'application mobile dans <code>mobile/src/services/api.js</code></li>';
            echo '</ul>';
            echo '</div>';
        } else {
            echo '<div class="highlight">';
            echo '<h3>⚠️ Configuration Incomplète</h3>';
            echo '<p>Certains éléments nécessitent votre attention. Consultez les détails ci-dessus.</p>';
            echo '</div>';
        }
        ?>
    </div>

    <!-- LINKS -->
    <div class="card">
        <h2>🔗 Liens Utiles</h2>
        <ul>
            <li><a href="DEPLOYMENT_GUIDE.md">📖 Guide de Déploiement Complet</a></li>
            <li><a href="login.php">🔐 Page de Connexion</a></li>
            <li><a href="test_db.php">🔍 Test Détaillé de la Base de Données</a></li>
            <li><a href="check_db_php.php">🔍 Vérification des Bases de Données</a></li>
        </ul>

        <div class="highlight">
            <strong>⚠️ Sécurité :</strong> Supprimez ce fichier et tous les fichiers de test une fois le déploiement terminé :
            <br><code>rm -f VERIFY_STATUS.php test_db.php check_db_php.php debug_login.php repair_db.php *.sh</code>
        </div>
    </div>

    <div style="text-align: center; color: #999; margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
        <p>Application de Gestion de Chantiers - Version 1.0</p>
        <p>Dernière vérification : <?php echo date('d/m/Y H:i:s'); ?></p>
    </div>
</body>
</html>
