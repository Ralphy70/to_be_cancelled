<?php
/**
 * Debug de la connexion depuis login.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug connexion login.php</h1>";

echo "<h2>Étape 1: Chargement des fichiers</h2>";

try {
    require_once 'config/config.php';
    echo "✅ config/config.php chargé<br>";
} catch (Exception $e) {
    echo "❌ Erreur config.php: " . $e->getMessage() . "<br>";
    exit;
}

try {
    require_once 'config/database.php';
    echo "✅ config/database.php chargé<br>";
} catch (Exception $e) {
    echo "❌ Erreur database.php: " . $e->getMessage() . "<br>";
    exit;
}

try {
    require_once 'includes/functions.php';
    echo "✅ includes/functions.php chargé<br>";
} catch (Exception $e) {
    echo "❌ Erreur functions.php: " . $e->getMessage() . "<br>";
    exit;
}

echo "<h2>Étape 2: Vérification des constantes</h2>";
echo "DB_HOST: <code>" . DB_HOST . "</code><br>";
echo "DB_NAME: <code>" . DB_NAME . "</code><br>";
echo "DB_USER: <code>" . DB_USER . "</code><br>";
echo "DB_PASS: <code>" . str_repeat('*', strlen(DB_PASS)) . "</code> (longueur: " . strlen(DB_PASS) . ")<br>";
echo "DB_CHARSET: <code>" . DB_CHARSET . "</code><br>";

echo "<h2>Étape 3: Test de connexion Database::getInstance()</h2>";

try {
    $db = Database::getInstance()->getConnection();
    echo "✅ <strong style='color: green;'>Connexion Database class réussie !</strong><br>";

    // Test de requête
    echo "<h2>Étape 4: Test de requête sur users</h2>";
    $stmt = $db->prepare("SELECT id, username, email, role FROM users WHERE username = :username OR email = :username");
    $stmt->execute(['username' => 'admin']);
    $user = $stmt->fetch();

    if ($user) {
        echo "✅ Utilisateur trouvé :<br>";
        echo "<pre>";
        print_r($user);
        echo "</pre>";
    } else {
        echo "❌ Aucun utilisateur 'admin' trouvé<br>";
    }

} catch (PDOException $e) {
    echo "❌ <strong style='color: red;'>Erreur PDO:</strong><br>";
    echo "Message: " . $e->getMessage() . "<br>";
    echo "Code: " . $e->getCode() . "<br>";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "<br>";
    echo "<pre>";
    echo $e->getTraceAsString();
    echo "</pre>";
} catch (Exception $e) {
    echo "❌ <strong style='color: red;'>Erreur générale:</strong><br>";
    echo "Message: " . $e->getMessage() . "<br>";
    echo "Code: " . $e->getCode() . "<br>";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "<br>";
    echo "<pre>";
    echo $e->getTraceAsString();
    echo "</pre>";
}

echo "<h2>Étape 5: Test fonction isLoggedIn()</h2>";
try {
    $result = isLoggedIn();
    echo "✅ Fonction isLoggedIn() = " . ($result ? 'true' : 'false') . "<br>";
} catch (Exception $e) {
    echo "❌ Erreur isLoggedIn(): " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h2>Résumé</h2>";
echo "<p>Si vous voyez ce message, les fichiers de configuration se chargent correctement.</p>";
echo "<p>Si login.php ne fonctionne toujours pas, il y a peut-être un problème avec :</p>";
echo "<ul>";
echo "<li>Les permissions des fichiers PHP</li>";
echo "<li>La configuration du serveur web (Apache/Nginx)</li>";
echo "<li>Un cache PHP (essayez de redémarrer PHP-FPM)</li>";
echo "</ul>";

echo "<hr>";
echo "<p><a href='login.php'>Tester login.php</a></p>";
?>
