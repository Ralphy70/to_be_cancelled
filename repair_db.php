<?php
/**
 * Script de réparation de la base de données
 * Ce script va supprimer et recréer toutes les tables
 */

$host = '127.0.0.1';
$dbname = 'gestion_chantiers';
$user = 'chantiers';
$pass = 'JhebGYv6n8nFF0lO0';

echo "<h1>Réparation de la base de données</h1>";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    echo "<h2>Étape 1: Suppression des tables corrompues</h2>";

    // Liste des tables à supprimer
    $tables = [
        'versements',
        'depenses',
        'chantier_financements',
        'documents',
        'postes_budgetaires',
        'financeurs',
        'chantiers',
        'users'
    ];

    foreach ($tables as $table) {
        try {
            $pdo->exec("DROP TABLE IF EXISTS `$table`");
            echo "✅ Table <code>$table</code> supprimée<br>";
        } catch (PDOException $e) {
            echo "⚠️ Erreur lors de la suppression de <code>$table</code>: " . $e->getMessage() . "<br>";
        }
    }

    echo "<h2>Étape 2: Recréation des tables</h2>";

    // Lire et exécuter le fichier schema.sql
    $schema_file = __DIR__ . '/database/schema.sql';

    if (!file_exists($schema_file)) {
        echo "❌ <strong style='color: red;'>Fichier schema.sql introuvable !</strong><br>";
        echo "Chemin recherché : <code>$schema_file</code><br>";
        exit;
    }

    $sql = file_get_contents($schema_file);

    // Séparer les requêtes (attention aux points-virgules dans les données)
    $sql = str_replace('USE gestion_chantiers;', '', $sql);
    $sql = str_replace('CREATE DATABASE IF NOT EXISTS gestion_chantiers CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;', '', $sql);

    // Exécuter le SQL
    try {
        $pdo->exec($sql);
        echo "✅ <strong style='color: green;'>Schéma importé avec succès !</strong><br>";
    } catch (PDOException $e) {
        echo "❌ <strong style='color: red;'>Erreur lors de l'import :</strong> " . $e->getMessage() . "<br>";
        echo "<p>Essayons méthode alternative...</p>";

        // Méthode alternative : exécuter via ligne de commande
        echo "<h3>Import via ligne de commande</h3>";
        echo "<p>Exécutez cette commande dans votre terminal :</p>";
        echo "<pre style='background: #f0f0f0; padding: 10px; border-radius: 5px;'>";
        echo "mysql -h 127.0.0.1 -u chantiers -pJhebGYv6n8nFF0lO0 gestion_chantiers &lt; database/schema.sql";
        echo "</pre>";
    }

    echo "<h2>Étape 3: Vérification</h2>";

    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "Tables créées (" . count($tables) . ") :<br>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";

    // Tester l'accès à la table users
    echo "<h2>Étape 4: Test d'accès aux tables</h2>";
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch();
        echo "✅ <strong style='color: green;'>Table users accessible ! Nombre d'utilisateurs : " . $result['count'] . "</strong><br>";

        // Vérifier si l'admin existe
        $stmt = $pdo->query("SELECT username, email FROM users WHERE role = 'admin' LIMIT 1");
        $admin = $stmt->fetch();
        if ($admin) {
            echo "✅ Utilisateur admin créé : <strong>" . $admin['username'] . "</strong> (" . $admin['email'] . ")<br>";
            echo "<p>🔑 Vous pouvez maintenant vous connecter avec :<br>";
            echo "- Username: <code>admin</code><br>";
            echo "- Password: <code>admin123</code></p>";
        }
    } catch (PDOException $e) {
        echo "❌ <strong style='color: red;'>Table users toujours inaccessible :</strong> " . $e->getMessage() . "<br>";
    }

    echo "<hr>";
    echo "<h2>✅ Réparation terminée !</h2>";
    echo "<p><a href='login.php' style='display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px;'>Aller à la page de connexion</a></p>";

} catch (PDOException $e) {
    echo "❌ <strong style='color: red;'>Erreur de connexion :</strong> " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<p><strong>⚠️ IMPORTANT :</strong> Supprimez ce fichier après la réparation !</p>";
echo "<p>Commande : <code>rm repair_db.php</code></p>";
?>
