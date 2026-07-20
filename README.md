# SmartNeti - Professional Network Management & Billing System

![SmartNeti](ui/ui/images/smartneti-logo.png)

SmartNeti is a comprehensive, feature-rich network management and billing system built on PHPNuxBill, designed for Mikrotik-based hotspot and PPPOE networks. It provides a complete solution for managing customers, plans, payments, vouchers, and network operations with a modern, responsive interface.

## 🌟 Key Features

### Core Network Management
- **Multi-Router Support**: Manage multiple Mikrotik routers simultaneously
- **Hotspot & PPPOE**: Full support for both hotspot authentication and PPPOE connections
- **FreeRADIUS Integration**: Complete FreeRADIUS database integration for advanced authentication
- **Device Management**: Configure and manage network devices and routers
- **Bandwidth Management**: Control and monitor bandwidth allocation per plan

### Customer Management
- **Self-Registration**: Allow customers to register their own accounts
- **User Balance System**: Customers maintain balance for purchases and renewals
- **Auto-Renewal**: Automatic package renewal using customer balance
- **Customer Groups**: Organize customers into groups for better management
- **Account Status**: Manage customer status (Active, Banned, Expired)
- **SMS Validation**: Optional SMS validation for secure login
- **Password Recovery**: Built-in forgot password functionality

### Voucher System
- **Voucher Generator**: Generate bulk vouchers with customizable parameters
- **Voucher Printing**: Print vouchers in various formats for physical distribution
- **Voucher-Only Login**: Allow login using vouchers without account registration
- **Voucher Activation**: Activate vouchers directly from login page
- **Voucher Management**: Track voucher usage, expiration, and status
- **QR Code Support**: Generate QR codes for vouchers

### Billing & Payments
- **Payment Gateway Integration**: Support for multiple payment gateways
- **Plugin Manager**: Install and manage payment gateway plugins
- **Invoice Generation**: Automatic invoice creation for all transactions
- **Payment History**: Complete transaction history and reporting
- **Balance Management**: Customer balance top-up and deduction
- **Currency Support**: Multi-currency support with configurable rates

### Plan Management
- **Flexible Plans**: Create unlimited internet plans with various parameters
- **Plan Categories**: Organize plans into categories for better organization
- **Time-Based Plans**: Hourly, daily, weekly, monthly, and yearly plans
- **Data-Based Plans**: Plans based on data usage limits
- **Unlimited Plans**: Unlimited data/time plans
- **Plan Templates**: Reusable plan templates for quick setup

### Reporting & Analytics
- **Revenue Reports**: Detailed revenue analysis by date, period, and plan
- **Customer Reports**: Customer registration and activity reports
- **Usage Reports**: Track customer usage patterns
- **Payment Reports**: Complete payment transaction reports
- **Daily/Weekly/Monthly Reports**: Flexible reporting periods
- **Export to CSV**: Export reports for external analysis

### Notification System
- **WhatsApp Notifications**: Send notifications to customers via WhatsApp
- **Telegram Notifications**: Admin notifications via Telegram bot
- **Email Notifications**: Email alerts for various events
- **SMS Notifications**: SMS alerts for important events
- **Custom Notifications**: Configure notification rules and templates

### Admin Features
- **Role-Based Access**: SuperAdmin, Admin, and other role types
- **Admin Activity Logs**: Track all admin actions for security
- **System Logs**: Comprehensive system logging for debugging
- **Settings Management**: Configure all system settings from admin panel
- **Theme Customization**: Customize login page, wallpaper, and branding
- **Logo Management**: Upload custom logos for branding
- **Maintenance Mode**: Enable maintenance mode during updates

### User Interface
- **Modern Dashboard**: Clean, responsive admin dashboard
- **Dark Mode**: Built-in dark/light mode toggle
- **Mobile Responsive**: Fully responsive design for all devices
- **Multi-Language**: Support for multiple languages
- **Custom Login Pages**: Customizable login page templates
- **Professional Design**: Modern, professional UI/UX design

### Advanced Features
- **Multi-Location Support**: Per-hotspot scoping for vouchers, plans, and payments
- **API Access**: RESTful API for integration with other systems
- **Webhook Support**: Webhook notifications for external integrations
- **Cron Job Management**: Automated task scheduling
- **Backup System**: Database backup and restore functionality
- **Update System**: Built-in update mechanism for easy upgrades
- **Plugin System**: Extensible plugin architecture

## 🚀 Installation

### System Requirements

**Minimum Requirements:**
- Linux or Windows OS (Linux recommended for cron jobs)
- PHP 8.2 or higher
- MySQL 4.1.x or higher / MariaDB
- PHP PDO & MySQLi Support
- PHP-GD2 Image Library
- PHP-CURL
- PHP-ZIP
- PHP-Mbstring
- Web Server (Apache/Nginx)

**Can be installed on:**
- Linux servers (recommended)
- Windows servers (cron job setup required)
- Raspberry Pi devices

### Quick Installation

1. **Download and Extract**
   ```bash
   git clone https://github.com/your-repo/smartneti.git
   cd smartneti
   ```

2. **Configure Database**
   - Create a MySQL database
   - Import the provided SQL file
   - Update `config.php` with your database credentials

3. **Set Permissions**
   ```bash
   chmod 755 system/
   chmod 755 system/uploads/
   chmod 755 pages/
   ```

4. **Configure Web Server**
   - Point your web server to the SmartNeti directory
   - Ensure `.htaccess` is enabled (Apache)

5. **Run Installation**
   - Access `http://your-domain.com/install` in your browser
   - Follow the installation wizard

6. **Setup Cron Jobs**
   ```bash
   # Run every minute
   * * * * * php /path/to/smartneti/system/cron.php
   ```

### Docker Installation

```bash
docker-compose up -d
```

See `docker-compose.example.yml` for configuration options.

## 📖 Usage Guide

### Admin Panel

Access the admin panel at `/admin` with your admin credentials.

**Key Admin Functions:**
- **Dashboard**: Overview of system status and statistics
- **Customers**: Manage customer accounts
- **Plans**: Create and manage internet plans
- **Vouchers**: Generate and manage vouchers
- **Payments**: View payment history and manage transactions
- **Reports**: Generate various reports
- **Settings**: Configure system settings
- **Routers**: Manage Mikrotik routers
- **Logs**: View system and activity logs

### Customer Panel

Customers can access their panel at `/home` to:
- View their account details
- Purchase internet plans
- Check balance and recharge
- View usage history
- Manage their profile

### Voucher System

1. **Generate Vouchers**: Go to Admin → Vouchers → Generate
2. **Print Vouchers**: Select vouchers and print them
3. **Activate Vouchers**: Customers can activate vouchers from login page

### Payment Integration

1. **Install Payment Gateway**: Admin → Settings → Plugin Manager
2. **Configure Gateway**: Enter your API credentials
3. **Test Payment**: Use test mode to verify setup
4. **Go Live**: Switch to production mode

## 🔧 Configuration

### Database Configuration

Edit `config.php`:
```php
$db_host = "localhost";
$db_user = "your_db_user";
$db_pass = "your_db_password";
$db_name = "smartneti";
```

### Mikrotik Router Configuration

1. Add your router in Admin → Routers
2. Configure API credentials
3. Set up hotspot/PPPOE on Mikrotik
4. Test connection

### Payment Gateway Configuration

Each payment gateway has specific configuration requirements. Refer to the gateway documentation for detailed setup instructions.

## 🌐 Multi-Location Support

SmartNeti supports multi-location deployments with per-hotspot scoping:

- **Location-Based Vouchers**: Vouchers can be restricted to specific hotspots
- **Location-Based Plans**: Plans can be assigned to specific locations
- **Location Analytics**: Separate analytics per hotspot
- **Location-Based Payments**: Track payments by location

## 🔒 Security Features

- **CSRF Protection**: Built-in CSRF token validation
- **Password Hashing**: Secure password storage using modern hashing
- **Session Management**: Secure session handling
- **Access Control**: Role-based access control
- **Activity Logging**: Complete audit trail of all actions
- **SQL Injection Protection**: Parameterized queries throughout

## 📊 Reporting

### Available Reports

- **Revenue Reports**: Daily, weekly, monthly revenue analysis
- **Customer Reports**: Registration, active users, churn analysis
- **Usage Reports**: Bandwidth usage, connection time analysis
- **Payment Reports**: Payment gateway performance, transaction analysis
- **Voucher Reports**: Voucher generation, usage, expiration tracking

### Export Options

All reports can be exported to CSV for further analysis.

## 🎨 Customization

### Branding

- **Custom Logo**: Upload your company logo
- **Custom Favicon**: Set your favicon
- **Login Page**: Customize login page design
- **Wallpaper**: Set custom login page wallpaper
- **Color Scheme**: Modify color scheme via CSS

### Themes

SmartNeti includes built-in themes and supports custom theme development.

### Language

Add new languages by creating language files in the `system/lang/` directory.

## 🔄 Updates

### Automatic Updates

SmartNeti includes a built-in update mechanism:

1. Go to Admin → Settings → Update
2. Check for updates
3. Download and install updates automatically

### Manual Updates

```bash
git pull origin main
php system/update.php
```

## 🐛 Troubleshooting

### Common Issues

**CSS/JS Not Loading:**
- Check file permissions
- Verify `APP_URL` in config.php
- Clear browser cache

**Database Connection Issues:**
- Verify database credentials
- Check MySQL server status
- Ensure database exists

**Mikrotik Connection Issues:**
- Verify API credentials
- Check router IP and port
- Ensure API service is enabled on Mikrotik

**Cron Jobs Not Running:**
- Verify cron job syntax
- Check PHP path
- Review cron job logs

## 📞 Support

### Community Support

- **GitHub Discussions**: Ask questions and get help from the community
- **Documentation**: Comprehensive documentation available
- **Issue Tracker**: Report bugs and request features

### Professional Support

For professional technical support, contact the development team.

## 📝 License

SmartNeti is released under the GNU General Public License version 2 or later.

See [LICENSE](LICENSE) file for details.

## 🤝 Contributing

Contributions are welcome! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 🙏 Acknowledgments

SmartNeti is built upon PHPNuxBill, an excellent Mikrotik billing system. We thank the original developers and all contributors who have made this project possible.

## 📄 Changelog

See [CHANGELOG.md](CHANGELOG.md) for detailed version history and changes.

## 🔗 Links

- **Documentation**: [Full Documentation](docs/index.html)
- **API Documentation**: [API Reference](docs/index.html)
- **Payment Gateways**: [Available Gateways](https://github.com/orgs/hotspotbilling/repositories?q=payment+gateway)
- **Plugins**: [Available Plugins](https://github.com/orgs/hotspotbilling/repositories?q=plugin)

---

**SmartNeti** - Professional Network Management & Billing System

Built for performance, security, and ease of use.
