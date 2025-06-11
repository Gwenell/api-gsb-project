#!/bin/bash

# Configuration
DB_USER="root"
DB_PASS="212002"
DB_NAME="gsbrapport"
EXPORT_FILE="gsb_database_export_$(date +%Y%m%d).sql"

# Exporter la base de données complète
mysqldump -u $DB_USER -p$DB_PASS --add-drop-table --routines --events --triggers --databases $DB_NAME > $EXPORT_FILE

# Compresser le fichier
gzip -f $EXPORT_FILE

echo "Base de données exportée vers ${EXPORT_FILE}.gz" 