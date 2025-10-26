<?php
/**
 * Vérification des bases de données via PHP PDO
 */

$host = '127.0.0.1';
$user = 'chantiers';
$pass = 'JhebGYv6n8nFF0lO0';

echo "<h1>🔍 Vérification des bases de données</h1>";

// Test 1: Connexion sans database pour lister les bases
echo "<h2>Test 1: Liste des bases de données</h2>";
try {
    $pdo = new PDO("mysql:host=$host", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $stmt = $pdo->query("SHOW DATABASES");
    $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "Bases de données trouvées (" . count($databases) . ") :<br>";
    echo "<ul>";
    foreach ($databases as $db) {
        echo "<li><strong>$db</strong></li>";
    }
    echo "</ul>";

    // Chercher spécifiquement
    $has_chantiers = in_array('chantiers', $databases);
    $has_gestion = in_array('gestion_chantiers', $databases);

    echo "<br>";
    echo ($has_chantiers ? "✅" : "❌") . " Base <code>chantiers</code><br>";
    echo ($has_gestion ? "✅" : "❌") . " Base <code>gestion_chantiers</code><br>";

} catch (PDOException $e) {
    echo "❌ Erreur: " . $e->getMessage() . "<br>";
}

// Test 2: Connexion à 'chantiers'
echo "<h2>Test 2: Connexion à 'chantiers'</h2>";
try {
    $pdo = new PDO("mysql:host=$host;dbname=chantiers;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    echo "✅ <strong style='color: green;'>Connexion à 'chantiers' réussie !</strong><br>";

    // Compter les tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables trouvées : " . count($tables) . "<br>";

    // Compter les users
    if (in_array('users', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $count = $stmt->fetch()['count'];
        echo "Utilisateurs : $count<br>";
    }

} catch (PDOException $e) {
    echo "❌ Impossible de se connecter à 'chantiers'<br>";
    echo "Erreur: " . $e->getMessage() . "<br>";
}

// Test 3: Connexion à 'gestion_chantiers'
echo "<h2>Test 3: Connexion à 'gestion_chantiers'</h2>";
try {
    $pdo = new PDO("mysql:host=$host;dbname=gestion_chantiers;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    echo "✅ <strong style='color: green;'>Connexion à 'gestion_chantiers' réussie !</strong><br>";

    // Compter les tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables trouvées : " . count($tables) . "<br>";

    // Compter les users
    if (in_array('users', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $count = $stmt->fetch()['count'];
        echo "Utilisateurs : $count<br>";
    }

} catch (PDOException $e) {
    echo "❌ Impossible de se connecter à 'gestion_chantiers'<br>";
    echo "Erreur: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h2>📋 CONCLUSION</h2>";
echo "<p><strong>Utilisez le nom de base qui fonctionne ci-dessus.</strong></p>";
echo "<p>Mettez à jour :</p>";
echo "<ul>";
echo "<li><code>config/config.php</code> - ligne DB_NAME</li>";
echo "<li><code>api/config.php</code> - ligne DB_NAME</li>";
echo "</ul>";
?>
