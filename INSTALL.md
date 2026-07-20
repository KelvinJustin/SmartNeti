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

```bash
crontab -e
```

Select 1 for nano editor, then add:

```cron
0 */4 * * * cd /var/www/html/system/ && php -f cron.php
```

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
