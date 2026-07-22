# SmartNeti Troubleshooting Guide

## Common Issues and Solutions

### Internal Error - Database Schema Issues

**Symptoms:**
- "Internal Error" message when accessing admin panel
- PHP Fatal error: `PDOException: SQLSTATE[42S01]` in logs
- Error code `42S01` indicates "Base table or view not found" or duplicate table

**Cause:**
- Database tables missing or incomplete
- Schema incompatible with current version
- Partial installation or failed migration

**Solution 1: Fresh Database Installation (Recommended for new installs)**

```bash
# 1. Backup existing data (if needed)
mysqldump -u smartneti -p smartneti > smartneti_backup.sql

# 2. Drop and recreate database
mysql -u smartneti -p -e "DROP DATABASE smartneti;"
mysql -u smartneti -p -e "CREATE DATABASE smartneti;"
mysql -u smartneti -p -e "GRANT ALL PRIVILEGES ON smartneti.* TO 'smartneti'@'%'; FLUSH PRIVILEGES;"

# 3. Import fresh schema
mysql -u smartneti -p smartneti < /var/www/html/install/phpnuxbill.sql
mysql -u smartneti -p smartneti < /var/www/html/install/radius.sql

# 4. Verify tables
mysql -u smartneti -p smartneti -e "SHOW TABLES;"
```

**Solution 2: Force Import (Preserves existing data)**

```bash
# Import with --force flag to skip existing tables
mysql -u smartneti -p smartneti --force < /var/www/html/install/phpnuxbill.sql
mysql -u smartneti -p smartneti --force < /var/www/html/install/radius.sql
```

**Solution 3: Manual Table Check**

```bash
# Check which tables exist
mysql -u smartneti -p smartneti -e "SHOW TABLES;"

# If tables are missing, import the specific schema file
```

---

### PHP JIT Memory Warning

**Symptoms:**
- `PHP Warning: preg_match(): Allocation of JIT memory failed` in logs
- Performance issues with regex operations

**Cause:**
- PHP JIT memory limit too low
- PHP configuration issue

**Solution:**

Edit PHP configuration:
```bash
sudo nano /etc/php/8.2/apache2/php.ini
```

Add or modify:
```ini
opcache.jit_buffer_size=256M
```

Restart Apache:
```bash
sudo systemctl restart apache2
```

---

### Database Connection Issues

**Symptoms:**
- "Access denied for user" error
- "Can't connect to MySQL server"
- Connection timeout errors

**Solution:**

1. **Verify MySQL/MariaDB is running:**
```bash
sudo systemctl status mysql
# or
sudo systemctl status mariadb
```

2. **Check database credentials in config.php:**
```bash
sudo nano /var/www/html/config.php
```

Verify these settings:
```php
$db_host = "localhost";
$db_user = "smartneti";
$db_pass = "your_password";
$db_name = "smartneti";
```

3. **Test database connection:**
```bash
mysql -u smartneti -p smartneti -e "SELECT 1;"
```

4. **Check database user privileges:**
```bash
mysql -u root -p -e "SHOW GRANTS FOR 'smartneti'@'%';"
```

---

### File Permission Issues

**Symptoms:**
- 403 Forbidden errors
- Upload failures
- Cache write errors

**Solution:**

Set proper ownership and permissions:
```bash
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
sudo chmod -R 777 /var/www/html/system/uploads
sudo chmod -R 777 /var/www/html/system/cache
```

---

### CSS/JS Not Loading

**Symptoms:**
- Unstyled pages
- Broken layout
- JavaScript not working

**Solution:**

1. **Check file permissions** (see above)

2. **Verify APP_URL in config.php:**
```bash
sudo nano /var/www/html/config.php
```
Ensure `APP_URL` is set correctly:
```php
define('APP_URL', 'http://your-domain.com');
```

3. **Clear browser cache**
- Hard refresh: Ctrl+F5 (Windows) or Cmd+Shift+R (Mac)

4. **Check Apache rewrite module:**
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

---

### MikroTik Connection Issues

**Symptoms:**
- "Check Now" fails in Routers section
- Connection timeout
- API authentication errors

**Solution:**

1. **Test network connectivity:**
```bash
ping <mikrotik-ip>
telnet <mikrotik-ip> 8728
```

2. **Verify API service on MikroTik:**
```mikrotik
/ip service print
```
Ensure API is enabled and running on port 8728.

3. **Check firewall rules on MikroTik:**
```mikrotik
/ip firewall filter print
```
Allow traffic from SmartNeti server IP to MikroTik API port.

4. **Verify credentials:**
- Username and password must be correct
- User must have API permissions in MikroTik

---

### Cron Job Not Running

**Symptoms:**
- Automatic tasks not executing
- Account expiration not working
- Balance deductions not processing

**Solution:**

1. **Check if cron job exists:**
```bash
crontab -l
```

2. **Add cron job if missing:**
```bash
crontab -e
```
Add:
```cron
0 */4 * * * cd /var/www/html/system/ && php -f cron.php
```

3. **Verify PHP path:**
```bash
which php
```
Use the full path if needed:
```cron
0 */4 * * * cd /var/www/html/system/ && /usr/bin/php -f cron.php
```

4. **Check cron logs:**
```bash
sudo grep CRON /var/log/syslog
```

---

### Hotspot Login Redirect Issues

**Symptoms:**
- Hotspot login page not redirecting to SmartNeti
- Login loops
- Authentication failures

**Solution:**

1. **Check login.html on MikroTik:**
```bash
# Download current login.html
# Copy to MikroTik hotspot directory
```

Ensure redirect code is correct:
```html
$(if error)
<meta http-equiv="refresh" content="0; url=$(link-orig)&msg=$(error)&nux-key=$(chap-id)-$(chap-challenge)">
$(else)
<meta http-equiv="refresh" content="0; url=http://YOUR_SMARTNETI_IP/?nux-mac=$(mac)&nux-ip=$(ip)&nux-hostname=$(hostname)&nux-router=1&nux-key=$(chap-id)-$(chap-challenge)">
$(endif)
```

2. **Verify Walled Garden entry:**
- Add SmartNeti server IP to Walled Garden
- Allow unauthenticated access to billing system

3. **Check Radius configuration:**
- Ensure "Use Radius" is enabled in Hotspot Profile
- Verify Radius client settings match NAS table

---

### Payment Gateway Issues

**Symptoms:**
- Payment processing failures
- Webhook not received
- Transaction not recorded

**Solution:**

1. **Check payment gateway configuration:**
- Verify API credentials
- Check webhook URL is accessible
- Ensure test mode is properly configured

2. **Check logs:**
```bash
tail -f /var/log/apache2/error.log
```

3. **Verify database tables:**
```bash
mysql -u smartneti -p smartneti -e "SHOW TABLES LIKE 'tbl_transactions';"
```

4. **Test payment gateway connection:**
- Use gateway's test mode
- Check gateway dashboard for error logs

---

### Performance Issues

**Symptoms:**
- Slow page loads
- High CPU usage
- Memory exhaustion

**Solution:**

1. **Enable PHP OPcache:**
```bash
sudo nano /etc/php/8.2/apache2/php.ini
```
Ensure:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=60
```

2. **Optimize MySQL:**
```bash
sudo nano /etc/mysql/mariadb.conf.d/50-server.cnf
```
Adjust memory settings based on available RAM.

3. **Clear cache:**
```bash
rm -rf /var/www/html/system/cache/*
rm -rf /var/www/html/ui/cache/*
```

4. **Check database indexes:**
```bash
mysql -u smartneti -p smartneti -e "SHOW INDEX FROM tbl_customers;"
```

---

## Getting Help

If you cannot resolve the issue:

1. **Check logs:**
```bash
tail -f /var/log/apache2/error.log
tail -f /var/log/apache2/access.log
```

2. **Enable debug mode in config.php:**
```php
define('DEBUG', true);
```

3. **Check GitHub Issues:**
- Search for similar issues
- Create a new issue with details

4. **Provide information when asking for help:**
- SmartNeti version
- PHP version
- MySQL/MariaDB version
- Error messages from logs
- Steps to reproduce the issue

---

## Backup and Recovery

### Regular Backups

**Database backup:**
```bash
mysqldump -u smartneti -p smartneti > backup_$(date +%Y%m%d).sql
```

**File backup:**
```bash
tar -czf smartneti_files_$(date +%Y%m%d).tar.gz /var/www/html
```

**Automated backup script:**
```bash
#!/bin/bash
# /usr/local/bin/backup_smartneti.sh
DATE=$(date +%Y%m%d)
mysqldump -u smartneti -pPASSWORD smartneti > /backups/db_$DATE.sql
tar -czf /backups/files_$DATE.tar.gz /var/www/html
find /backups -name "*.sql" -mtime +7 -delete
find /backups -name "*.tar.gz" -mtime +7 -delete
```

Add to cron:
```cron
0 2 * * * /usr/local/bin/backup_smartneti.sh
```

### Restore from Backup

**Database restore:**
```bash
mysql -u smartneti -p smartneti < backup_YYYYMMDD.sql
```

**File restore:**
```bash
tar -xzf smartneti_files_YYYYMMDD.tar.gz -C /
```

---

## Security Checklist

- [ ] Change default admin password
- [ ] Use strong database passwords
- [ ] Enable SSL/TLS (HTTPS)
- [ ] Configure firewall rules
- [ ] Keep system updated
- [ ] Regular backups
- [ ] Monitor logs regularly
- [ ] Use secure file permissions
- [ ] Disable debug mode in production
- [ ] Keep payment gateway credentials secure
