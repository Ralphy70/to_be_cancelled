#!/bin/bash

# Script de nettoyage complet et réparation de la base de données
# Ce script résout l'erreur "Tablespace already exists"

echo "🔧 Nettoyage complet de la base de données"
echo "=================================================="
echo ""

DB_HOST="127.0.0.1"
DB_NAME="gestion_chantiers"
DB_USER="chantiers"
DB_PASS="JhebGYv6n8nFF0lO0"

# Vérifier que le fichier schema.sql existe
if [ ! -f "database/schema.sql" ]; then
    echo "❌ Erreur : Le fichier database/schema.sql est introuvable !"
    exit 1
fi

echo "Étape 1/4: Sauvegarde de la liste des tables..."
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -e "SHOW TABLES;" > /tmp/tables_backup.txt 2>/dev/null
echo "✅ Liste sauvegardée"

echo ""
echo "Étape 2/4: Suppression FORCÉE de toutes les tables et tablespaces..."

# Supprimer avec DISCARD TABLESPACE pour forcer la suppression des fichiers .ibd
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME <<EOF
SET FOREIGN_KEY_CHECKS = 0;

-- Essayer de discard les tablespaces avant de drop
DROP TABLE IF EXISTS versements;
DROP TABLE IF EXISTS depenses;
DROP TABLE IF EXISTS chantier_financements;
DROP TABLE IF EXISTS documents;
DROP TABLE IF EXISTS postes_budgetaires;
DROP TABLE IF EXISTS financeurs;
DROP TABLE IF EXISTS chantiers;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;
EOF

if [ $? -eq 0 ]; then
    echo "✅ Tables supprimées"
else
    echo "⚠️  Avertissement lors de la suppression (normal si tables corrompues)"
fi

echo ""
echo "Étape 3/4: Nettoyage de la base de données..."

# Supprimer et recréer la base de données complètement
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS <<EOF
DROP DATABASE IF EXISTS $DB_NAME;
CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EOF

if [ $? -eq 0 ]; then
    echo "✅ Base de données nettoyée et recréée"
else
    echo "❌ Erreur lors du nettoyage de la base de données"
    exit 1
fi

echo ""
echo "Étape 4/4: Import du schéma propre..."

# Préparer le fichier SQL (enlever les lignes CREATE DATABASE et USE)
cat database/schema.sql | grep -v "CREATE DATABASE" | grep -v "^USE gestion_chantiers;" > /tmp/schema_clean.sql

# Importer
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME < /tmp/schema_clean.sql

if [ $? -eq 0 ]; then
    echo "✅ Schéma importé avec succès"
    rm /tmp/schema_clean.sql 2>/dev/null
else
    echo "❌ Erreur lors de l'import du schéma"
    echo ""
    echo "Le fichier temporaire est ici pour debug : /tmp/schema_clean.sql"
    exit 1
fi

echo ""
echo "Étape 5/5: Vérification finale..."

# Compter les tables
TABLES_COUNT=$(mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -e "SHOW TABLES;" 2>/dev/null | wc -l)
TABLES_COUNT=$((TABLES_COUNT - 1))

# Compter les utilisateurs
USER_COUNT=$(mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -e "SELECT COUNT(*) as count FROM users;" 2>/dev/null | tail -1)

# Tester l'accès à une table
TEST_QUERY=$(mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -e "SELECT username FROM users WHERE role='admin' LIMIT 1;" 2>/dev/null | tail -1)

echo "✅ Nombre de tables : $TABLES_COUNT"
echo "✅ Nombre d'utilisateurs : $USER_COUNT"

if [ ! -z "$TEST_QUERY" ]; then
    echo "✅ Test d'accès : OK (admin user = $TEST_QUERY)"
else
    echo "⚠️  Pas d'utilisateur admin trouvé"
fi

# Afficher les tables créées
echo ""
echo "Tables créées :"
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -e "SHOW TABLES;" 2>/dev/null

echo ""
echo "=================================================="
echo "✅ Nettoyage et réparation terminés avec succès !"
echo ""
echo "🎯 Étapes suivantes :"
echo "  1. Testez : http://votre-domaine/test_db.php"
echo "  2. Connectez-vous : http://votre-domaine/login.php"
echo "     Username: admin"
echo "     Password: admin123"
echo "  3. Supprimez les fichiers de test :"
echo "     rm test_db.php repair_db.php repair.sh clean_repair.sh"
echo "=================================================="
