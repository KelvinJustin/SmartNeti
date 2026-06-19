#!/bin/bash
set -e

FLAG="/var/lib/mysql/.smartneti-initialized"

if [[ -f $FLAG ]]; then
  echo "SmartNeti database already initialized. Skipping..."
  exit 0
fi

echo "Initializing SmartNeti RadiusDesk database..."

# Configure timezone data (ignore duplicates if already loaded)
mysql_tzinfo_to_sql /usr/share/zoneinfo | mysql -u root -f mysql 2>/dev/null || true

# Configure privileges
mysql -u root < /docker-entrypoint-initdb.d/db_priveleges.sql

# Import RadiusDesk tables
mysql -u root rd < /docker-entrypoint-initdb.d/rd.sql

# Apply patches in alphabetical order
for patch in /docker-entrypoint-initdb.d/db_patches/*.sql; do
  filename=$(basename "$patch")

  # Skip main files
  if [[ "$filename" == "rd.sql" || "$filename" == "rd.min.sql" ]]; then
    echo "Skipping: $filename"
    continue
  fi

  if [ -f "$patch" ]; then
    echo "Applying patch: $filename"
    mysql -u root rd < "$patch"
  fi
done

# Create SmartNeti separate database
mysql -u root -e "CREATE DATABASE IF NOT EXISTS smartneti;"
mysql -u root -e "GRANT ALL PRIVILEGES ON smartneti.* TO 'rd'@'%';"
mysql -u root -e "FLUSH PRIVILEGES;"

# Apply SmartNeti schema (safe to re-run, uses CREATE TABLE IF NOT EXISTS)
mysql -u root smartneti < /docker-entrypoint-initdb.d/02_create_smartneti.sql

# Apply SmartNeti migrations in alphabetical order
for migration in /docker-entrypoint-initdb.d/migrations/*.sql; do
  if [ -f "$migration" ]; then
    filename=$(basename "$migration")
    echo "Applying SmartNeti migration: $filename"
    mysql -u root smartneti < "$migration"
  fi
done

touch "$FLAG"

echo "SmartNeti database initialization complete."
