# Application de Gestion de Chantiers Immobiliers

Application web complète pour la gestion des comptes et budgets de chantiers immobiliers, développée en PHP/MySQL.

## Fonctionnalités principales

### 1. Gestion des chantiers
- Création et suivi de chantiers immobiliers
- Informations détaillées : nom, adresse, dates, budget total
- Statuts multiples : planification, en cours, suspendu, terminé, annulé
- Tableaux de bord avec statistiques en temps réel

### 2. Gestion budgétaire par postes
- Création de postes budgétaires principaux
- Support des sous-catégories hiérarchiques (postes imbriqués)
- Allocation et suivi des budgets par poste
- Visualisation de la consommation budgétaire avec barres de progression
- Alertes visuelles quand le budget atteint 75% et 90%

### 3. Gestion des dépenses
- Enregistrement détaillé des dépenses par poste
- Suivi des fournisseurs et numéros de facture
- Trois statuts : prévue, engagée, payée
- Calcul automatique du budget consommé

### 4. Multi-financement
- Gestion de plusieurs financeurs par chantier
- Types de financeurs : banque, investisseur, subvention, fonds propres, autre
- Suivi des montants prévus vs versés
- Historique détaillé des versements
- Calcul automatique des pourcentages de participation

### 5. Gestion des utilisateurs
- Système d'authentification sécurisé
- Deux rôles : administrateur et utilisateur standard
- Les administrateurs peuvent gérer tous les chantiers et utilisateurs
- Les utilisateurs standards ne peuvent gérer que leurs propres chantiers

## Structure de la base de données

### Tables principales

#### `users`
- Gestion des utilisateurs avec authentification
- Rôles : admin / user

#### `chantiers`
- Informations principales des chantiers
- Statuts et dates de suivi
- Lié à un utilisateur responsable

#### `postes_budgetaires`
- Postes budgétaires avec support hiérarchique (parent_id)
- Budget alloué et consommé
- Lié à un chantier

#### `depenses`
- Dépenses détaillées par poste
- Statuts : prévue, engagée, payée
- Informations fournisseur et facture

#### `financeurs`
- Catalogue des financeurs
- Informations de contact

#### `chantier_financements`
- Liaison entre chantiers et financeurs
- Montants prévus et versés
- Conditions et statuts

#### `versements`
- Historique détaillé des versements
- Références et notes

#### `documents` (optionnel)
- Stockage des métadonnées de documents attachés

## Installation

### Prérequis
- Serveur web (Apache/Nginx)
- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Extensions PHP : PDO, pdo_mysql

### Étapes d'installation

1. **Cloner ou télécharger l'application**
   ```bash
   git clone <url-repo>
   cd gestion-chantiers
   ```

2. **Configurer la base de données**
   - Créer une base de données MySQL
   - Importer le schéma :
     ```bash
     mysql -u root -p < database/schema.sql
     ```

3. **Configurer l'application**
   - Éditer le fichier `config/config.php`
   - Modifier les paramètres de connexion à la base de données :
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'gestion_chantiers');
     define('DB_USER', 'votre_utilisateur');
     define('DB_PASS', 'votre_mot_de_passe');
     ```

4. **Configurer le serveur web**
   - Pour Apache, utiliser le fichier `.htaccess` fourni
   - Pointer le DocumentRoot vers le dossier de l'application
   - Exemple de configuration Apache :
     ```apache
     <VirtualHost *:80>
         ServerName gestion-chantiers.local
         DocumentRoot /var/www/gestion-chantiers
         <Directory /var/www/gestion-chantiers>
             AllowOverride All
             Require all granted
         </Directory>
     </VirtualHost>
     ```

5. **Définir les permissions**
   ```bash
   chmod 755 -R .
   ```

6. **Accéder à l'application**
   - Ouvrir votre navigateur
   - Aller à l'URL configurée
   - Se connecter avec les identifiants par défaut :
     - **Utilisateur** : admin
     - **Mot de passe** : admin123

⚠️ **Important** : Changez immédiatement le mot de passe administrateur par défaut !

## Structure des fichiers

```
gestion-chantiers/
├── assets/
│   └── css/
│       └── style.css              # Styles CSS de l'application
├── config/
│   ├── config.php                 # Configuration générale
│   └── database.php               # Classe de connexion à la BD
├── database/
│   └── schema.sql                 # Schéma de la base de données
├── includes/
│   ├── header.php                 # En-tête des pages
│   ├── footer.php                 # Pied de page
│   └── functions.php              # Fonctions utilitaires
├── index.php                      # Tableau de bord
├── login.php                      # Page de connexion
├── logout.php                     # Déconnexion
├── chantiers.php                  # Liste des chantiers
├── chantier_add.php               # Ajouter un chantier
├── chantier_edit.php              # Modifier un chantier
├── chantier_view.php              # Détails d'un chantier
├── poste_add.php                  # Ajouter un poste budgétaire
├── poste_view.php                 # Détails d'un poste
├── depense_add.php                # Ajouter une dépense
├── depense_edit.php               # Modifier une dépense
├── financeurs.php                 # Liste des financeurs
├── financeur_add.php              # Ajouter un financeur
├── financeur_edit.php             # Modifier un financeur
├── financement_add.php            # Ajouter un financement
├── financement_view.php           # Détails d'un financement
├── versement_add.php              # Ajouter un versement
├── users.php                      # Gestion des utilisateurs (admin)
├── user_add.php                   # Ajouter un utilisateur (admin)
├── user_edit.php                  # Modifier un utilisateur (admin)
└── README.md                      # Ce fichier
```

## Utilisation

### Premier pas
1. Connectez-vous avec le compte admin
2. Créez des financeurs dans "Financeurs"
3. Créez un nouveau chantier
4. Ajoutez des postes budgétaires au chantier
5. Associez des financements au chantier
6. Enregistrez les dépenses par poste

### Workflow typique

1. **Planification**
   - Créer un chantier
   - Définir les postes budgétaires principaux
   - Créer des sous-catégories si nécessaire
   - Allouer les budgets

2. **Financement**
   - Ajouter les financeurs
   - Créer les financements pour le chantier
   - Définir les montants prévus
   - Enregistrer les versements au fur et à mesure

3. **Suivi**
   - Enregistrer les dépenses réelles
   - Suivre la consommation budgétaire
   - Vérifier les alertes (75%, 90%)
   - Consulter les rapports

### Gestion des postes hiérarchiques

Exemple de hiérarchie :
```
Chantier : Immeuble Paris 15e
├── Gros œuvre (100 000 €)
│   ├── Fondations (30 000 €)
│   ├── Murs porteurs (50 000 €)
│   └── Planchers (20 000 €)
├── Second œuvre (80 000 €)
│   ├── Électricité (25 000 €)
│   ├── Plomberie (30 000 €)
│   └── Menuiseries (25 000 €)
└── Finitions (50 000 €)
```

## Sécurité

- Mots de passe hashés avec `password_hash()` (bcrypt)
- Protection contre les injections SQL (requêtes préparées PDO)
- Échappement des sorties HTML
- Vérification des droits d'accès sur chaque page
- Sessions PHP sécurisées

## Personnalisation

### Modifier les couleurs
Éditez `assets/css/style.css` et modifiez les variables CSS :
```css
:root {
    --primary-color: #2c3e50;
    --secondary-color: #3498db;
    --success-color: #27ae60;
    /* ... */
}
```

### Ajouter des champs
1. Modifier le schéma de la base de données
2. Mettre à jour les formulaires concernés
3. Adapter les requêtes SQL

## Support et contribution

Pour signaler un bug ou suggérer une amélioration, veuillez créer une issue sur le dépôt du projet.

## Licence

Cette application est fournie telle quelle, à des fins éducatives et professionnelles.

## Auteur

Application développée pour la gestion de chantiers immobiliers.

---

**Note** : Cette application est un exemple de système de gestion. Pour une utilisation en production, assurez-vous de :
- Activer HTTPS
- Désactiver l'affichage des erreurs PHP
- Sauvegarder régulièrement la base de données
- Mettre en place un système de logs
- Ajouter une validation côté client (JavaScript)
