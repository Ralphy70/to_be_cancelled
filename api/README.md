# API REST - Gestion de Chantiers

API REST en PHP pour l'application de gestion de chantiers immobiliers.

## Configuration

### Installation

1. Placez le dossier `api/` dans votre répertoire web
2. Configurez la connexion à la base de données dans `api/config.php`
3. Assurez-vous que la base de données est créée et importée

### Configuration de base

Éditez `api/config.php` :

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'gestion_chantiers');
define('DB_USER', 'votre_utilisateur');
define('DB_PASS', 'votre_mot_de_passe');
define('JWT_SECRET_KEY', 'changez_cette_cle_secrete');
```

## Authentification

L'API utilise JWT (JSON Web Tokens) pour l'authentification.

### Obtenir un token

**Endpoint** : `POST /api/auth.php`

**Request** :
```json
{
  "username": "admin",
  "password": "admin123"
}
```

**Response** :
```json
{
  "success": true,
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {
    "id": 1,
    "username": "admin",
    "email": "admin@example.com",
    "role": "admin"
  }
}
```

### Utiliser le token

Incluez le token dans l'en-tête `Authorization` de toutes les requêtes :

```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

## Endpoints

### Statistiques

**GET /api/stats.php**

Récupère les statistiques du tableau de bord.

**Response** :
```json
{
  "success": true,
  "stats": {
    "total_chantiers": 5,
    "chantiers_en_cours": 2,
    "budget_total": 500000.00,
    "total_verse": 350000.00
  }
}
```

### Chantiers

**GET /api/chantiers.php**

Liste tous les chantiers (selon les droits de l'utilisateur).

**GET /api/chantiers.php?id={id}**

Récupère un chantier spécifique avec ses statistiques.

**POST /api/chantiers.php**

Crée un nouveau chantier.

**Request** :
```json
{
  "nom": "Résidence des Jardins",
  "description": "Construction de 50 logements",
  "adresse": "12 Rue des Fleurs, Paris",
  "date_debut": "2024-01-01",
  "date_fin_prevue": "2025-12-31",
  "budget_total": 5000000.00,
  "statut": "planification"
}
```

**PUT /api/chantiers.php?id={id}**

Modifie un chantier existant.

**DELETE /api/chantiers.php?id={id}**

Supprime un chantier.

### Postes budgétaires

**GET /api/postes.php?chantier_id={id}**

Liste les postes d'un chantier.

**GET /api/postes.php?id={id}**

Récupère un poste avec ses sous-postes et dépenses.

**POST /api/postes.php**

Crée un poste budgétaire.

**Request** :
```json
{
  "chantier_id": 1,
  "nom": "Gros œuvre",
  "description": "Fondations et structure",
  "budget_alloue": 100000.00,
  "parent_id": null
}
```

**PUT /api/postes.php?id={id}**

Modifie un poste.

### Dépenses

**POST /api/depenses.php**

Crée une dépense.

**Request** :
```json
{
  "poste_id": 1,
  "description": "Béton et ferraillage",
  "montant": 15000.00,
  "date_depense": "2024-03-15",
  "fournisseur": "Béton SA",
  "numero_facture": "F-2024-001",
  "statut": "payee"
}
```

**PUT /api/depenses.php?id={id}**

Modifie une dépense.

### Financeurs

**GET /api/financeurs.php**

Liste tous les financeurs.

**POST /api/financeurs.php**

Crée un financeur.

**Request** :
```json
{
  "nom": "Banque Nationale",
  "type": "banque",
  "contact_nom": "Jean Dupont",
  "contact_email": "j.dupont@banque.com",
  "contact_telephone": "0123456789"
}
```

### Financements

**GET /api/financements.php?chantier_id={id}**

Liste les financements d'un chantier.

**GET /api/financements.php?id={id}**

Récupère un financement avec ses versements.

**POST /api/financements.php**

Crée un financement.

**Request** :
```json
{
  "chantier_id": 1,
  "financeur_id": 1,
  "montant_prevu": 300000.00,
  "montant_verse": 0,
  "pourcentage_participation": 60,
  "date_accord": "2024-01-15",
  "conditions": "Taux d'intérêt de 2.5%",
  "statut": "accorde"
}
```

### Versements

**POST /api/versements.php**

Enregistre un versement.

**Request** :
```json
{
  "financement_id": 1,
  "montant": 50000.00,
  "date_versement": "2024-02-01",
  "reference": "VIR-2024-001",
  "notes": "Premier versement"
}
```

## Codes de réponse HTTP

- `200` - Succès
- `201` - Créé
- `400` - Requête invalide
- `401` - Non authentifié
- `403` - Accès refusé
- `404` - Non trouvé
- `405` - Méthode non autorisée
- `500` - Erreur serveur

## Gestion des erreurs

Toutes les erreurs retournent un JSON avec la clé `error` :

```json
{
  "error": "Message d'erreur"
}
```

## CORS

Les headers CORS sont configurés pour autoriser toutes les origines (`*`). En production, limitez à votre domaine :

```php
header('Access-Control-Allow-Origin: https://votre-domaine.com');
```

## Sécurité

### Recommandations pour la production

1. **Changez la clé JWT** dans `config.php`
2. **Désactivez l'affichage des erreurs** :
   ```php
   error_reporting(0);
   ini_set('display_errors', 0);
   ```
3. **Activez HTTPS** uniquement
4. **Limitez les CORS** à votre domaine
5. **Utilisez des secrets forts**
6. **Mettez en place un rate limiting**
7. **Loggez les tentatives de connexion**

### JWT

- Les tokens expirent après 24 heures
- Le secret JWT doit être gardé confidentiel
- En production, utilisez une librairie JWT complète (ex: firebase/php-jwt)

## Testing

### Avec cURL

```bash
# Login
curl -X POST http://localhost/api/auth.php \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'

# Get chantiers (avec token)
curl -X GET http://localhost/api/chantiers.php \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Avec Postman

1. Importez la collection depuis `/api/postman_collection.json` (à créer)
2. Configurez la variable d'environnement `base_url`
3. Connectez-vous pour obtenir un token
4. Le token sera automatiquement ajouté aux requêtes suivantes

## Performance

- Utilisez un cache Redis pour les requêtes fréquentes
- Indexez les colonnes de recherche dans MySQL
- Activez la compression gzip
- Utilisez un CDN pour les assets statiques

## Logs

Les erreurs sont loggées via le système d'erreurs PHP. Consultez les logs :

- Apache : `/var/log/apache2/error.log`
- Nginx : `/var/log/nginx/error.log`
- PHP-FPM : `/var/log/php-fpm/error.log`

---

**Documentation de l'API REST pour Gestion de Chantiers**
