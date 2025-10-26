#!/bin/bash

# Vérifier quelles bases de données existent

echo "🔍 Vérification des bases de données MySQL"
echo "=========================================="
echo ""

DB_USER="chantiers"
DB_PASS="JhebGYv6n8nFF0lO0"

echo "Liste de TOUTES les bases de données accessibles :"
echo ""

mysql -h 127.0.0.1 -u $DB_USER -p$DB_PASS -e "SHOW DATABASES;" 2>/dev/null

echo ""
echo "Recherche spécifique :"
echo ""

# Vérifier si 'chantiers' existe
if mysql -h 127.0.0.1 -u $DB_USER -p$DB_PASS -e "USE chantiers;" 2>/dev/null; then
    echo "✅ Base 'chantiers' existe"
    TABLE_COUNT=$(mysql -h 127.0.0.1 -u $DB_USER -p$DB_PASS chantiers -e "SHOW TABLES;" 2>/dev/null | wc -l)
    echo "   Nombre de tables : $((TABLE_COUNT - 1))"
else
    echo "❌ Base 'chantiers' n'existe PAS"
fi

echo ""

# Vérifier si 'chantiers' (deuxième vérification)
if mysql -h 127.0.0.1 -u $DB_USER -p$DB_PASS -e "USE chantiers;" 2>/dev/null; then
    echo "✅ Base 'chantiers' existe (vérification 2)"
    TABLE_COUNT=$(mysql -h 127.0.0.1 -u $DB_USER -p$DB_PASS chantiers -e "SHOW TABLES;" 2>/dev/null | wc -l)
    echo "   Nombre de tables : $((TABLE_COUNT - 1))"

    # Compter les users
    USER_COUNT=$(mysql -h 127.0.0.1 -u $DB_USER -p$DB_PASS chantiers -e "SELECT COUNT(*) FROM users;" 2>/dev/null | tail -1)
    echo "   Nombre d'utilisateurs : $USER_COUNT"
fi

echo ""
echo "=========================================="
echo "CONCLUSION :"
echo ""
echo "Utilisez le nom de base qui existe et qui contient des données."
echo "Mettez à jour tous vos fichiers de config avec ce nom."
