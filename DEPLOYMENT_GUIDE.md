# Guide de Déploiement - Application Gestion de Chantiers

## 📋 État actuel

✅ **Tous les fichiers sont configurés et committés** avec le nom de base de données correct : `chantiers`

### Configuration confirmée :

**config/config.php** :
```php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'chantiers');  // ✅ Correct
define('DB_USER', 'chantiers');
define('DB_PASS', 'JhebGYv6n8nFF0lO0');
```

**api/config.php** :
```php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'chantiers');  // ✅ Correct
define('DB_USER', 'chantiers');
define('DB_PASS', 'JhebGYv6n8nFF0lO0');
```

**Scripts de réparation** :
- `repair.sh` : DB_NAME="chantiers" ✅
- `clean_repair.sh` : DB_NAME="chantiers" ✅
- `check_databases.sh` : DB_USER="chantiers" ✅

---

## 🚀 Étapes de Déploiement sur votre Serveur

### Étape 1 : Récupérer les fichiers mis à jour

Sur votre serveur, dans le répertoire de l'application :

```bash
cd /chemin/vers/votre/application
git pull origin claude/construction-project-management-app-011CUUhmKszrkbSu6BUjQcs1
```

### Étape 2 : Vérifier la base de données

Accédez à : `http://votre-domaine/check_db_php.php`

Ce fichier va :
- ✅ Lister toutes les bases de données accessibles
- ✅ Tester la connexion à `chantiers`
- ✅ Tester la connexion à `gestion_chantiers` (pour comparaison)
- ✅ Afficher le nombre de tables et d'utilisateurs

**Résultat attendu** : La base `chantiers` doit fonctionner avec des tables et des utilisateurs.

### Étape 3 : Réparer la base si nécessaire

Si la base de données a des problèmes (tables corrompues, tablespace errors) :

```bash
bash clean_repair.sh
```

Ce script va :
1. Sauvegarder la liste des tables
2. Supprimer et recréer complètement la base `chantiers`
3. Importer le schéma depuis `database/schema.sql`
4. Créer l'utilisateur admin par défaut

### Étape 4 : Tester l'application web

Accédez à : `http://votre-domaine/login.php`

**Identifiants par défaut** :
- Username : `admin`
- Password : `admin123`

Si tout fonctionne, vous devriez être redirigé vers le dashboard.

### Étape 5 : Tester l'API REST (pour l'app mobile)

```bash
curl -X POST http://votre-domaine/api/auth.php \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'
```

Vous devriez recevoir un token JWT.

---

## 🔍 Fichiers de Diagnostic Disponibles

Tous ces fichiers sont maintenant dans le repo :

| Fichier | Description | Accès |
|---------|-------------|-------|
| `check_db_php.php` | Vérification complète des bases de données | http://votre-domaine/check_db_php.php |
| `test_db.php` | Test de connexion détaillé | http://votre-domaine/test_db.php |
| `debug_login.php` | Debug du processus de login | http://votre-domaine/debug_login.php |
| `check_databases.sh` | Vérification via ligne de commande | `bash check_databases.sh` |
| `repair.sh` | Réparation simple des tables | `bash repair.sh` |
| `clean_repair.sh` | Nettoyage complet et réparation | `bash clean_repair.sh` |

---

## 🧹 Nettoyage après Déploiement

**⚠️ IMPORTANT** : Supprimez les fichiers de diagnostic une fois tout fonctionnel (sécurité) :

```bash
rm -f test_db.php debug_login.php check_db_php.php repair_db.php
rm -f repair.sh clean_repair.sh check_databases.sh
```

---

## 📱 Configuration de l'Application Mobile

### Étape 1 : Mettre à jour l'URL de l'API

Éditez `mobile/src/services/api.js` :

```javascript
const API_BASE_URL = 'http://VOTRE_IP_OU_DOMAINE/gestion-chantiers/api';
```

Remplacez par votre IP serveur réelle (ex: `http://192.168.1.100/gestion-chantiers/api`)

### Étape 2 : Installer les dépendances

```bash
cd mobile
npm install
```

### Étape 3 : Lancer l'application mobile

```bash
npx expo start
```

Scannez le QR code avec :
- **Android** : Expo Go app
- **iOS** : Camera app

---

## 🎯 Derniers Commits

```
b47308d - Update all configs to use 'chantiers' database name
2c233f5 - Add database verification scripts
c91b7d7 - Add debug script for login connection troubleshooting
1789e77 - Add complete database cleanup script
f7d03c8 - Add database repair scripts for table corruption issues
```

---

## ❓ En cas de Problème

### Erreur : "Erreur de connexion à la base de données"

1. Vérifiez que vous avez bien fait `git pull`
2. Exécutez `check_db_php.php` pour identifier le problème
3. Si nécessaire, exécutez `bash clean_repair.sh`

### Erreur : "Table doesn't exist in engine"

Cela indique des tables corrompues. Solution :

```bash
bash clean_repair.sh
```

### L'application mobile ne se connecte pas

1. Vérifiez que l'API URL dans `api.js` est correcte
2. Testez l'API avec curl (voir Étape 5)
3. Vérifiez que votre téléphone et serveur sont sur le même réseau
4. Pour iOS, utilisez HTTPS en production (certificat SSL requis)

---

## ✅ Checklist de Déploiement

- [ ] Faire `git pull` sur le serveur
- [ ] Vérifier avec `check_db_php.php`
- [ ] Si besoin, réparer avec `bash clean_repair.sh`
- [ ] Tester login.php (admin/admin123)
- [ ] Tester l'API avec curl
- [ ] Configurer l'URL API mobile
- [ ] Tester l'app mobile
- [ ] Supprimer les fichiers de diagnostic
- [ ] Changer le mot de passe admin
- [ ] Changer JWT_SECRET_KEY en production

---

## 📊 Structure de la Base de Données

La base `chantiers` contient 8 tables :

1. **users** - Utilisateurs (admin/user)
2. **chantiers** - Projets de construction
3. **postes_budgetaires** - Postes budgétaires hiérarchiques
4. **depenses** - Dépenses par poste
5. **financeurs** - Organismes financeurs
6. **chantier_financements** - Liens chantier-financeur
7. **versements** - Paiements reçus
8. **documents** - Documents attachés aux chantiers

---

**Date de dernière mise à jour** : 2025-10-26
**Branche Git** : `claude/construction-project-management-app-011CUUhmKszrkbSu6BUjQcs1`
