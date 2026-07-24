# SmartNeti Installation & Configuration Guide

## Prepare Server

### Install MySQL, PHP and Apache2

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y apache2
sudo apt install -y mariadb-server mariadb-client
sudo apt install -y php php-mysqli php-pdo php-gd php-curl php-mysql
```

### Download SmartNeti

Download latest from GitHub, unzip and transfer to server:

```bash
wget https://github.com/KelvinJustin/SmartNeti/archive/refs/heads/master.zip
```

Unzip and move contents to default Apache2 directory (and remove default index file):

```bash
sudo apt install unzip
unzip master.zip
sudo mv SmartNeti-master/* /var/www/html/
sudo rm /var/www/html/index.html
```

## PHPMyAdmin and Database Installation

### Add a global (root) MySQL User

User: `master`  
Password: `f!rstShip68`

```bash
sudo mysql -u root -p
```

```sql
CREATE USER 'master'@'localhost' IDENTIFIED BY 'f!rstShip68';
GRANT ALL PRIVILEGES ON *.* TO 'master'@'localhost' WITH GRANT OPTION;
FLUSH PRIVILEGES;
quit
```

### Install PHPMyAdmin for ease of Database configuration

```bash
sudo apt install -y phpmyadmin
```

**Note:** During install select apache2 as the webserver

### Create SmartNeti blank DB and User

**Option 1 – Via PhpMyAdmin**

Log into PHPMyAdmin via `http://<server ip>/phpmyadmin` and go to User accounts and add a new one. Select a Username and Password and check the boxes to:
- Create database with same name
- Grant all privileges
- Grant all privileges on wildcard name

**Option 2 – via MySQL**

User: `smartneti`  
Password: `smartneti`

```sql
CREATE USER 'smartneti'@'%' IDENTIFIED BY 'smartneti';
CREATE DATABASE smartneti;
GRANT ALL PRIVILEGES ON smartneti.* TO 'smartneti'@'%';
FLUSH PRIVILEGES;
```

## Database Configuration

### Manual Configuration

Copy the SQL tables for SmartNeti and Radius by using the Global User (master) created earlier:

```bash
mysql -u master -p smartneti < /var/www/html/install/phpnuxbill.sql 
mysql -u master -p smartneti < /var/www/html/install/radius.sql
```

The database will now be populated with all the relevant tables.

## FreeRADIUS Installation and Configuration

### Install FreeRADIUS

```bash
sudo apt update
sudo apt install -y freeradius freeradius-mysql freeradius-utils
```

### Import FreeRADIUS Schema into Existing Database

Import the RADIUS tables into your existing SmartNeti database:

```bash
sudo cat /etc/freeradius/3.0/mods-config/sql/main/mysql/schema.sql | sudo mysql -u root -p smartneti
```

This will add the RADIUS tables (radcheck, radreply, radacct, nas, etc.) to your existing database without affecting your current data.

### Enable SQL Module

```bash
sudo ln -s /etc/freeradius/3.0/mods-available/sql /etc/freeradius/3.0/mods-enabled/
```

### Configure SQL Module

Edit the SQL module configuration:

```bash
sudo nano /etc/freeradius/3.0/mods-enabled/sql
```

Find and modify these settings to match your SmartNeti database credentials:

```sql
sql {
    driver = "rlm_sql_mysql"
    dialect = "mysql"
    
    server = "localhost"
    port = 3306
    login = "smartneti"
    password = "your_db_password"
    radius_db = "smartneti"
    
    read_clients = yes
    client_table = "nas"
    
    # Comment out TLS section
    #tls {
    #    ca_file = "/etc/ssl/certs/my_ca.crt"
    #    ...
    #}
    warnings = auto
}
```

### Configure Sites-Enabled Default

Edit the default sites configuration:

```bash
sudo nano /etc/freeradius/3.0/sites-enabled/default
```

Add these to the respective sections:

**authorize section:**
```
authorize {
    ...
    sql
    expiration
    quotalimit
    accessperiod
    uptimelimit
    
    if (User-Name){
        if("%{sql:UPDATE radacct set AcctStopTime=ADDDATE(AcctStartTime,INTERVAL AcctSessionTime SECOND), AcctTerminateCause='Clear-Stale Session' WHERE UserName='%{User-Name}' and CallingStationId='%{Calling-Station-Id}' and AcctStopTime is NULL}"){
        }
    }
    ...
}
```

**accounting section:**
```
accounting {
    ...
    sql
    ...
}
```

**post-auth section:**
```
post-auth {
    ...
    sql
    ...
}
```

**session section:**
```
session {
    ...
    sql
    ...
}
```

### Configure SQL Counters

Edit the SQL counter configuration:

```bash
sudo nano /etc/freeradius/3.0/mods-available/sqlcounter
```

Add at the end:

```
sqlcounter accessperiod {
    sql_module_instance = sql
    dialect = ${modules.sql.dialect}
    counter_name = Max-Access-Period-Time
    check_name = Access-Period
    key = User-Name
    reset = never
    $INCLUDE ${modconfdir}/sql/counter/${dialect}/${.:instance}.conf
}

sqlcounter quotalimit {
    sql_module_instance = sql
    dialect = ${modules.sql.dialect}
    counter_name = Max-Volume
    check_name = Max-Data
    reply_name = Mikrotik-Total-Limit
    key = User-Name
    reset = never
    $INCLUDE ${modconfdir}/sql/counter/${dialect}/${.:instance}.conf
}

sqlcounter uptimelimit {
    counter_name = 'Max-All-Session-Time'
    check_name = 'Max-All-Session'
    sql_module_instance = sql
    key = 'User-Name'
    reset = never
    query = "SELECT SUM(AcctSessionTime) FROM radacct WHERE UserName='%{${key}}'"
}
```

### Create Counter Configuration Files

Create the access period counter configuration:

```bash
sudo nano /etc/freeradius/3.0/mods-config/sql/counter/mysql/accessperiod.conf
```

Add:
```
query = "\
SELECT UNIX_TIMESTAMP() - UNIX_TIMESTAMP(AcctStartTime) \
FROM radacct \
WHERE UserName='%{${key}}' \
ORDER BY AcctStartTime LIMIT 1"
```

Create the quota limit counter configuration:

```bash
sudo nano /etc/freeradius/3.0/mods-config/sql/counter/mysql/quotalimit.conf
```

Add:
```
query = "\
SELECT (SUM(acctinputoctets) + SUM(acctoutputoctets)) \
FROM radacct \
WHERE UserName='%{${key}}'"
```

### Enable SQL Counter Module

```bash
sudo ln -s /etc/freeradius/3.0/mods-available/sqlcounter /etc/freeradius/3.0/mods-enabled/
```

### Fix Permissions

```bash
sudo chgrp -h freerad /etc/freeradius/3.0/mods-available/sql
sudo chown -R freerad:freerad /etc/freeradius/3.0/mods-enabled/sql
```

### Test Configuration

Stop the service and test in debug mode:

```bash
sudo systemctl stop freeradius
sudo freeradius -X
```

If no errors appear, press Ctrl+C to stop and start normally:

```bash
sudo systemctl start freeradius
sudo systemctl enable freeradius
```

### Update SmartNeti Config with RADIUS Settings

Edit your SmartNeti config file:

```bash
sudo nano /var/www/html/config.php
```

Add these lines after the database configuration:

```php
// Database Radius
$radius_host        = 'localhost';
$radius_user        = 'smartneti';
$radius_pass        = 'your_db_password';
$radius_name        = 'smartneti';
```

## SmartNeti Configuration

### Copy sample Config file and rename it

```bash
sudo cp /var/www/html/config.sample.php /var/www/html/config.php
```

### Edit the configuration file

```bash
sudo nano /var/www/html/config.php
```

Update the Database Settings:

```php
$db_host    = "localhost"; # Database Host
$db_port    = "";          # Database Port. Keep it blank if you are unsure.
$db_user    = "smartneti"; # Database Username
$db_pass    = "your_secure_password"; # Database Password
$db_name    = "smartneti"; # Database Name
```

### Set Apache2 user to own all files

```bash
sudo chown -R www-data:www-data /var/www/html
```

## Add Cron Job

The cron job must run as the `www-data` user to avoid lock file permission errors. Use the system cron directory:

```bash
sudo nano /etc/cron.d/phpnuxbill
```

Add this line:

```cron
* * * * * www-data php /var/www/html/system/cron.php >/dev/null 2>&1
```

Then reload cron:

```bash
sudo systemctl restart cron
```

**Note:** The `www-data` field specifies which user should run the command, so there's no need for sudo. This ensures the cron job has proper permissions to create and access the lock file.

## SmartNeti GUI

Access via: `http://<server IP>/admin`

Default Username/Password = `admin` / `admin`

## Add the MikroTik Device

1. Go to **Network > Routers**
2. Add Name, IP address and Username and password for the MikroTik (or a dedicated user account added to System > Users on the MikroTik)
3. Click **Check Now** to enable Online Checking

**Note:** Test you can ping and even SSH to the MikroTik from the Server CLI before doing this step to confirm you have access.

## Add MikroTik as a Radius Client (NAS)

1. Go to PHPMyAdmin and click on the `nas` table
2. Click Insert
3. Add:
   - **Nasname**: IP or resolvable Hostname of MikroTik
   - **Shortname**: Identifiable name for the MikroTik
   - **Secret**: Needs to match on the MikroTik
   - **Routers**: This is the Router Name / Location set on the SmartNeti Routers section

## MikroTik Configuration

### Radius Client

On the MikroTik go to Radius and add:

1. Select `ppp` and `hotspot`
2. Add the address of the SmartNeti server
3. Set the Secret to match what you set in the `nas` table

### Hotspot Setup

1. Go to **IP > Hotspot**, click **Hotspot Setup**
2. Select the Guest SSID network
3. Confirm IP of VLAN interface
4. Specify IP pool to give to clients
5. Select certificate (none)
6. Follow the wizard prompts to complete setup

### Login Redirect

Download the `hotspot/login.html` file from the MikroTik and replace it with this:

```html
$(if error)
<meta http-equiv="refresh" content="0; url=$(link-orig)&msg=$(error)&nux-key=$(chap-id)-$(chap-challenge)">
$(else)
<meta http-equiv="refresh" content="0; url=http://YOUR_SMARTNETI_IP/?nux-mac=$(mac)&nux-ip=$(ip)&nux-hostname=$(hostname)&nux-router=1&nux-key=$(chap-id)-$(chap-challenge)">
$(endif)
```

**Important:** Replace `YOUR_SMARTNETI_IP` with the IP address of your SmartNeti server.

### Configure Hotspot Profile

1. Edit the Hotspot Profile
2. Go to the **Radius** tab
3. Enable **Use Radius**

### Add Walled Garden Entry

Add a Walled Garden entry for the IP of the SmartNeti server as the Dst. Host to allow unauthenticated access to the billing system.

## Troubleshooting

### Connection Issues

- Ensure you can ping the MikroTik from the SmartNeti server
- Verify firewall rules allow communication between server and MikroTik
- Check that the API service is enabled on MikroTik

### Database Issues

- Verify MySQL/MariaDB is running
- Check database credentials in config.php
- Ensure the database user has proper privileges

### Cron Job Issues

- Verify PHP path: `which php`
- Check cron job syntax
- Review system logs for errors

## Security Recommendations

1. **Change Default Passwords**: Change the default admin password immediately after installation
2. **Use Strong Database Passwords**: Use complex passwords for database users
3. **Enable SSL/TLS**: Use HTTPS for production deployments
4. **Firewall Configuration**: Configure firewall rules to restrict access
5. **Regular Updates**: Keep the system and dependencies updated
6. **Backup Strategy**: Implement regular database backups

## Additional Configuration

For more advanced configuration options, refer to the main README.md file and documentation.
