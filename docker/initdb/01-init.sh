#!/bin/bash
set -e

FLAG="/bitnami/mariadb/.smartneti-initialized"

if [[ -f $FLAG ]]; then
  echo "SmartNeti database already initialized. Skipping..."
  exit 0
fi

echo "Initializing SmartNeti RadiusDesk database..."

# Configure timezone data
mysql_tzinfo_to_sql /usr/share/zoneinfo | mysql -u root mysql

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

touch "$FLAG"

echo "SmartNeti database initialization complete."
