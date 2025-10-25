# Guide d'installation - Gestion de Chantiers

Ce guide vous accompagne pas à pas dans l'installation de l'application.

## Prérequis système

- **Serveur web** : Apache 2.4+ ou Nginx
- **PHP** : Version 7.4 ou supérieure
- **MySQL** : Version 5.7 ou supérieure (ou MariaDB 10.2+)
- **Extensions PHP requises** :
  - pdo
  - pdo_mysql
  - session
  - json

## Installation détaillée

### 1. Préparation de l'environnement

#### Sur un serveur local (XAMPP, WAMP, MAMP)

1. Téléchargez et installez XAMPP/WAMP/MAMP
2. Démarrez Apache et MySQL
3. Placez les fichiers de l'application dans le dossier `htdocs` (XAMPP) ou `www` (WAMP)

#### Sur un serveur Linux

```bash
# Installation d'Apache, PHP et MySQL sur Ubuntu/Debian
sudo apt update
sudo apt install apache2 php mysql-server php-mysql php-pdo
sudo systemctl start apache2
sudo systemctl start mysql
```

### 2. Configuration de la base de données

#### Option A : Via phpMyAdmin

1. Accédez à phpMyAdmin (http://localhost/phpmyadmin)
2. Créez une nouvelle base de données nommée `gestion_chantiers`
3. Importez le fichier `database/schema.sql`
4. Créez un utilisateur dédié (recommandé) :
   ```sql
   CREATE USER 'gestion_app'@'localhost' IDENTIFIED BY 'votre_mot_de_passe';
   GRANT ALL PRIVILEGES ON gestion_chantiers.* TO 'gestion_app'@'localhost';
   FLUSH PRIVILEGES;
   ```

#### Option B : En ligne de commande

```bash
# Se connecter à MySQL
mysql -u root -p

# Dans le shell MySQL :
CREATE DATABASE gestion_chantiers CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'gestion_app'@'localhost' IDENTIFIED BY 'votre_mot_de_passe';
GRANT ALL PRIVILEGES ON gestion_chantiers.* TO 'gestion_app'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Importer le schéma
mysql -u root -p gestion_chantiers < database/schema.sql
```

### 3. Configuration de l'application

Éditez le fichier `config/config.php` :

```php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'gestion_chantiers');
define('DB_USER', 'gestion_app');          // Votre utilisateur
define('DB_PASS', 'votre_mot_de_passe');   // Votre mot de passe
```

### 4. Configuration du serveur web

#### Apache

Si vous n'utilisez pas le dossier racine, créez un VirtualHost :

```apache
<VirtualHost *:80>
    ServerName gestion-chantiers.local
    DocumentRoot /var/www/gestion-chantiers

    <Directory /var/www/gestion-chantiers>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/gestion-chantiers-error.log
    CustomLog ${APACHE_LOG_DIR}/gestion-chantiers-access.log combined
</VirtualHost>
```

Puis :
```bash
sudo a2ensite gestion-chantiers
sudo systemctl reload apache2
```

Ajoutez dans `/etc/hosts` :
```
127.0.0.1    gestion-chantiers.local
```

#### Nginx

Configuration exemple pour Nginx :

```nginx
server {
    listen 80;
    server_name gestion-chantiers.local;
    root /var/www/gestion-chantiers;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(ht|sql|md) {
        deny all;
    }
}
```

### 5. Permissions des fichiers

```bash
# Définir les permissions appropriées
sudo chown -R www-data:www-data /var/www/gestion-chantiers
sudo chmod -R 755 /var/www/gestion-chantiers
```

### 6. Première connexion

1. Ouvrez votre navigateur
2. Accédez à l'URL : `http://localhost/gestion-chantiers` ou `http://gestion-chantiers.local`
3. Connectez-vous avec :
   - **Utilisateur** : admin
   - **Mot de passe** : admin123

⚠️ **IMPORTANT** : Changez immédiatement le mot de passe admin !

### 7. Vérification de l'installation

Vérifiez que :
- [ ] La page de connexion s'affiche correctement
- [ ] Vous pouvez vous connecter
- [ ] Le tableau de bord s'affiche avec les statistiques
- [ ] Vous pouvez créer un chantier de test
- [ ] Les styles CSS sont bien chargés

## Dépannage

### Erreur : "Could not connect to database"

- Vérifiez les paramètres dans `config/config.php`
- Testez la connexion MySQL :
  ```bash
  mysql -u gestion_app -p gestion_chantiers
  ```

### Erreur : Page blanche

- Vérifiez les logs d'erreur PHP
- Activez temporairement l'affichage des erreurs dans `config/config.php`
- Vérifiez les permissions des fichiers

### Erreur 500

- Vérifiez la configuration Apache (.htaccess)
- Assurez-vous que mod_rewrite est activé :
  ```bash
  sudo a2enmod rewrite
  sudo systemctl restart apache2
  ```

### Les styles CSS ne se chargent pas

- Vérifiez le chemin dans le navigateur
- Vérifiez les permissions du dossier `assets/`
- Videz le cache du navigateur (Ctrl+F5)

## Sécurisation pour la production

Avant de déployer en production :

1. **Désactiver l'affichage des erreurs**
   ```php
   // Dans config/config.php
   error_reporting(0);
   ini_set('display_errors', 0);
   ```

2. **Activer HTTPS**
   - Obtenez un certificat SSL (Let's Encrypt gratuit)
   - Décommentez la redirection HTTPS dans `.htaccess`

3. **Changer tous les mots de passe par défaut**

4. **Limiter les tentatives de connexion**
   - Implémenter un système de limitation de tentatives

5. **Sauvegardes automatiques**
   ```bash
   # Exemple de script de sauvegarde (cron)
   mysqldump -u gestion_app -p gestion_chantiers > backup-$(date +%Y%m%d).sql
   ```

6. **Mettre à jour régulièrement**
   - PHP
   - MySQL
   - Dépendances

## Support

En cas de problème :
1. Consultez les logs d'erreur
2. Vérifiez la configuration
3. Reportez les bugs sur le dépôt du projet

---

**Bon déploiement !**
