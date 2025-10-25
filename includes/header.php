<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . APP_NAME : APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <a href="index.php" class="navbar-brand"><?php echo APP_NAME; ?></a>
            <ul class="navbar-menu">
                <li><a href="index.php">Tableau de bord</a></li>
                <li><a href="chantiers.php">Chantiers</a></li>
                <li><a href="financeurs.php">Financeurs</a></li>
                <?php if (isAdmin()): ?>
                    <li><a href="users.php">Utilisateurs</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Déconnexion (<?php echo cleanOutput($_SESSION['username']); ?>)</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <?php
        $flash = getFlashMessage();
        if ($flash):
        ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <?php echo cleanOutput($flash['message']); ?>
            </div>
        <?php endif; ?>
