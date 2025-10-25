# Application Mobile - Gestion de Chantiers

Application mobile React Native pour la gestion de chantiers immobiliers, compagnon de l'application web.

## Technologies utilisées

- **React Native** avec Expo
- **React Navigation** pour la navigation
- **React Native Paper** pour les composants UI
- **Axios** pour les appels API
- **AsyncStorage** pour le stockage local

## Prérequis

- Node.js 16+ et npm/yarn
- Expo CLI (`npm install -g expo-cli`)
- Pour iOS: Xcode et simulateur iOS
- Pour Android: Android Studio et émulateur Android
- OU l'application Expo Go sur votre smartphone

## Installation

### 1. Installer les dépendances

```bash
cd mobile
npm install
```

### 2. Configurer l'URL de l'API

Éditez le fichier `src/services/api.js` et modifiez `API_BASE_URL` :

```javascript
// Remplacez par l'adresse IP de votre serveur local
const API_BASE_URL = 'http://192.168.1.100/gestion-chantiers/api';
```

**Important :** Utilisez l'adresse IP de votre machine, pas `localhost` !

Pour trouver votre IP :
- **Windows** : `ipconfig` dans le terminal
- **macOS/Linux** : `ifconfig` ou `ip addr`

### 3. Démarrer l'API backend

Assurez-vous que votre serveur web (Apache/Nginx) est démarré et que l'API est accessible :

```bash
# Tester l'API
curl http://192.168.1.100/gestion-chantiers/api/auth.php
```

## Lancement de l'application

### Méthode 1 : Expo Go (la plus simple)

```bash
npm start
```

Puis :
1. Scannez le QR code avec l'app **Expo Go** (disponible sur l'App Store et Google Play)
2. L'application se charge sur votre téléphone

### Méthode 2 : Émulateur

```bash
# iOS (Mac seulement)
npm run ios

# Android
npm run android
```

### Méthode 3 : Web (pour tester)

```bash
npm run web
```

## Structure du projet

```
mobile/
├── App.js                    # Point d'entrée de l'application
├── app.json                  # Configuration Expo
├── package.json              # Dépendances
├── src/
│   ├── context/
│   │   └── AuthContext.js    # Contexte d'authentification
│   ├── services/
│   │   └── api.js            # Services API et configuration Axios
│   ├── screens/              # Écrans de l'application
│   │   ├── LoginScreen.js
│   │   ├── DashboardScreen.js
│   │   ├── ChantiersScreen.js
│   │   ├── ChantierDetailScreen.js
│   │   ├── ChantierFormScreen.js
│   │   ├── PosteDetailScreen.js
│   │   ├── PosteFormScreen.js
│   │   ├── DepenseFormScreen.js
│   │   ├── FinanceursScreen.js
│   │   ├── FinancementDetailScreen.js
│   │   └── ProfileScreen.js
│   └── utils/
│       └── helpers.js        # Fonctions utilitaires
```

## Fonctionnalités

### ✅ Authentification
- Connexion avec username/email et mot de passe
- Stockage sécurisé du token JWT
- Déconnexion

### ✅ Tableau de bord
- Statistiques en temps réel
- Total chantiers, chantiers en cours
- Budget total et fonds versés
- Pull-to-refresh

### ✅ Gestion des chantiers
- Liste de tous les chantiers
- Création de nouveaux chantiers
- Vue détaillée avec statistiques
- Statuts visuels avec couleurs

### ✅ Postes budgétaires
- Liste des postes par chantier
- Ajout de nouveaux postes
- Barres de progression
- Alertes visuelles (75%, 90%)

### ✅ Dépenses
- Ajout de dépenses par poste
- Liste des dépenses avec détails
- Calcul automatique du budget consommé

### ✅ Financements
- Liste des financeurs
- Détails des financements par chantier
- Historique des versements
- Suivi des montants versés

## Utilisation

### Première connexion

1. Assurez-vous que l'API est accessible
2. Lancez l'application
3. Connectez-vous avec :
   - **Username** : admin
   - **Password** : admin123

### Navigation

L'application utilise une navigation par onglets en bas de l'écran :
- **Tableau de bord** : Vue d'ensemble
- **Chantiers** : Gestion des chantiers
- **Financeurs** : Liste des financeurs
- **Profil** : Informations utilisateur et déconnexion

### Workflow typique

1. **Créer un chantier** : Onglet Chantiers → Bouton +
2. **Voir les détails** : Tap sur un chantier
3. **Ajouter un poste** : Dans le détail → "Ajouter" dans la section Postes
4. **Ajouter une dépense** : Vue poste → "Ajouter" dans Dépenses

## Build pour production

### iOS (nécessite un Mac et un compte développeur Apple)

```bash
expo build:ios
```

### Android

```bash
expo build:android
```

## Dépannage

### L'API n'est pas accessible

- Vérifiez que votre serveur web est démarré
- Vérifiez l'adresse IP dans `api.js`
- Assurez-vous que votre téléphone/émulateur est sur le même réseau WiFi
- Testez l'URL de l'API dans un navigateur

### Erreur "Network request failed"

- Vérifiez les CORS dans l'API PHP (déjà configurés dans `api/config.php`)
- Vérifiez le firewall de votre machine

### L'application ne se charge pas

```bash
# Nettoyer le cache
expo start -c

# Réinstaller les dépendances
rm -rf node_modules
npm install
```

### Erreur de token expiré

- Déconnectez-vous et reconnectez-vous
- Le token JWT expire après 24 heures

## Différences avec l'application web

✅ **Disponible** :
- Consultation de tous les chantiers
- Ajout de chantiers, postes et dépenses
- Vue des financements
- Statistiques en temps réel

❌ **Non disponible** (utilisez la version web) :
- Modification de chantiers existants
- Gestion des utilisateurs (admin)
- Suppression d'éléments
- Ajout de financements et versements (prochainement)
- Upload de documents

## API Endpoints utilisés

L'application utilise l'API REST suivante :

- `POST /api/auth.php` - Authentification
- `GET /api/stats.php` - Statistiques
- `GET /api/chantiers.php` - Liste des chantiers
- `GET /api/chantiers.php?id=X` - Détails d'un chantier
- `POST /api/chantiers.php` - Créer un chantier
- `GET /api/postes.php?chantier_id=X` - Postes d'un chantier
- `POST /api/postes.php` - Créer un poste
- `POST /api/depenses.php` - Créer une dépense
- `GET /api/financeurs.php` - Liste des financeurs
- `GET /api/financements.php?chantier_id=X` - Financements

## Sécurité

- Les mots de passe ne sont jamais stockés en clair
- Le token JWT est stocké de manière sécurisée dans AsyncStorage
- Toutes les requêtes API utilisent le token d'authentification
- Les tokens expirent automatiquement après 24h

## Support

Pour les problèmes ou questions :
1. Vérifiez les logs dans la console Expo
2. Vérifiez les logs de l'API PHP sur le serveur
3. Consultez la documentation de l'API dans `/api/README.md`

---

**Bon développement mobile !**
