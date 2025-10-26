#!/bin/bash

# Script de réparation de la base de données
# Usage: bash repair.sh

echo "🔧 Réparation de la base de données gestion_chantiers"
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

echo "Étape 1/3: Suppression des tables existantes..."
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME <<EOF
SET FOREIGN_KEY_CHECKS = 0;
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
    echo "✅ Tables supprimées avec succès"
else
    echo "❌ Erreur lors de la suppression des tables"
    exit 1
fi

echo ""
echo "Étape 2/3: Import du schéma..."
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME < database/schema.sql

if [ $? -eq 0 ]; then
    echo "✅ Schéma importé avec succès"
else
    echo "❌ Erreur lors de l'import du schéma"
    exit 1
fi

echo ""
echo "Étape 3/3: Vérification..."
TABLES_COUNT=$(mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -e "SHOW TABLES;" | wc -l)
USER_COUNT=$(mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -e "SELECT COUNT(*) FROM users;" | tail -1)

echo "✅ Nombre de tables : $((TABLES_COUNT - 1))"
echo "✅ Nombre d'utilisateurs : $USER_COUNT"

if [ $USER_COUNT -gt 0 ]; then
    echo ""
    echo "=================================================="
    echo "✅ Réparation terminée avec succès !"
    echo ""
    echo "Vous pouvez maintenant vous connecter avec :"
    echo "  Username: admin"
    echo "  Password: admin123"
    echo "=================================================="
else
    echo ""
    echo "⚠️  Attention : Aucun utilisateur trouvé !"
    echo "Vérifiez que le fichier schema.sql contient bien les données de test."
fi

echo ""
echo "🧹 N'oubliez pas de supprimer les fichiers de test :"
echo "  rm test_db.php repair_db.php repair.sh"
