#!/bin/bash

# Deploy latest DB dump to remote server
REMOTE_USER="root"
REMOTE_HOST="154.26.133.175"
REMOTE_DB="admin_albalu"
DUMP_DIR="/home/davidecat/Desktop/Work/albalu-sviluppo/db_dumps"

# Find the latest dump file
LATEST_DUMP=$(ls -t "$DUMP_DIR"/*.sql 2>/dev/null | head -1)

if [ -z "$LATEST_DUMP" ]; then
    echo "ERROR: No SQL dump found in $DUMP_DIR"
    exit 1
fi

echo "Latest dump: $LATEST_DUMP"
echo "Copying to $REMOTE_USER@$REMOTE_HOST..."

scp "$LATEST_DUMP" "$REMOTE_USER@$REMOTE_HOST:/tmp/db_import.sql"

if [ $? -ne 0 ]; then
    echo "ERROR: Failed to copy dump to remote server"
    exit 1
fi

echo "Importing into database '$REMOTE_DB'..."

ssh "$REMOTE_USER@$REMOTE_HOST" "mysql '$REMOTE_DB' < /tmp/db_import.sql && rm /tmp/db_import.sql"

if [ $? -eq 0 ]; then
    echo "Database imported successfully!"
else
    echo "ERROR: Database import failed!"
    exit 1
fi
