<?php
/**
 * Fichier de test de connexion à la base de données
 * Accédez à ce fichier via : http://votre-domaine/test_db.php
 */

// Configuration
$host = '127.0.0.1';
$dbname = 'gestion_chantiers';
$user = 'chantiers';
$pass = 'JhebGYv6n8nFF0lO0';

echo "<h1>Test de connexion à la base de données</h1>";

// Test 1: Connexion PDO
echo "<h2>Test 1: Connexion PDO</h2>";
try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "✅ <strong style='color: green;'>Connexion PDO réussie !</strong><br>";

    // Test de requête
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "✅ Nombre d'utilisateurs dans la base : " . $result['count'] . "<br>";

} catch (PDOException $e) {
    echo "❌ <strong style='color: red;'>Erreur PDO :</strong> " . $e->getMessage() . "<br>";
}

// Test 2: Vérifier les tables
echo "<h2>Test 2: Vérification des tables</h2>";
try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "Tables trouvées (" . count($tables) . ") :<br>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";

    // Vérifier les tables requises
    $required_tables = [
        'users', 'chantiers', 'postes_budgetaires',
        'depenses', 'financeurs', 'chantier_financements',
        'versements', 'documents'
    ];

    $missing_tables = array_diff($required_tables, $tables);

    if (empty($missing_tables)) {
        echo "✅ <strong style='color: green;'>Toutes les tables requises sont présentes</strong><br>";
    } else {
        echo "⚠️ <strong style='color: orange;'>Tables manquantes :</strong><br>";
        echo "<ul>";
        foreach ($missing_tables as $table) {
            echo "<li style='color: orange;'>$table</li>";
        }
        echo "</ul>";
        echo "<p>💡 Importez le fichier <code>database/schema.sql</code> dans votre base de données.</p>";
    }

} catch (PDOException $e) {
    echo "❌ <strong style='color: red;'>Erreur :</strong> " . $e->getMessage() . "<br>";
}

// Test 3: Tester la connexion via la classe Database
echo "<h2>Test 3: Test via config/database.php</h2>";
try {
    require_once 'config/config.php';
    require_once 'config/database.php';

    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT VERSION() as version");
    $version = $stmt->fetch();

    echo "✅ <strong style='color: green;'>Connexion via Database class réussie !</strong><br>";
    echo "Version MySQL : " . $version['version'] . "<br>";

} catch (Exception $e) {
    echo "❌ <strong style='color: red;'>Erreur :</strong> " . $e->getMessage() . "<br>";
}

// Test 4: Vérifier l'utilisateur admin
echo "<h2>Test 4: Vérification utilisateur admin</h2>";
try {
    $stmt = $pdo->query("SELECT id, username, email, role FROM users WHERE role = 'admin' LIMIT 1");
    $admin = $stmt->fetch();

    if ($admin) {
        echo "✅ Utilisateur admin trouvé :<br>";
        echo "<ul>";
        echo "<li>ID: " . $admin['id'] . "</li>";
        echo "<li>Username: " . $admin['username'] . "</li>";
        echo "<li>Email: " . $admin['email'] . "</li>";
        echo "<li>Role: " . $admin['role'] . "</li>";
        echo "</ul>";
        echo "<p>🔑 Mot de passe par défaut : <code>admin123</code></p>";
    } else {
        echo "⚠️ <strong style='color: orange;'>Aucun utilisateur admin trouvé</strong><br>";
        echo "<p>Importez le fichier <code>database/schema.sql</code></p>";
    }

} catch (PDOException $e) {
    echo "❌ <strong style='color: red;'>Erreur :</strong> " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h2>Résumé de la configuration</h2>";
echo "<ul>";
echo "<li>Hôte : <code>$host</code></li>";
echo "<li>Base de données : <code>$dbname</code></li>";
echo "<li>Utilisateur : <code>$user</code></li>";
echo "<li>Mot de passe : <code>" . str_repeat('*', strlen($pass)) . "</code></li>";
echo "</ul>";

echo "<hr>";
echo "<p><strong>⚠️ IMPORTANT :</strong> Supprimez ce fichier après le test pour des raisons de sécurité !</p>";
echo "<p>Pour supprimer : <code>rm test_db.php</code></p>";
?>
