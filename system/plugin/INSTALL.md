# TumaSend Plugin Installation Guide

## Quick Installation

### Step 1: Upload Plugin Files

Upload the following files to your PHPNuxBill installation:

```
system/plugin/tumasend.php
system/plugin/tumasend/classes/Plugin.php
system/plugin/tumasend/classes/Config.php
system/plugin/tumasend/classes/API.php
system/plugin/tumasend/classes/SMS.php
system/plugin/tumasend/classes/Logger.php
system/plugin/tumasend/classes/Queue.php
system/plugin/ui/admin/tumasend-settings.tpl
system/plugin/ui/admin/tumasend-history.tpl
system/plugin/ui/admin/tumasend-templates.tpl
system/plugin/ui/admin/tumasend-diagnostics.tpl
system/cron_tumasend.php
```

### Step 2: Set File Permissions

Ensure the following directories are writable:

```bash
chmod 755 system/plugin/tumasend
chmod 755 system/plugin/tumasend/classes
chmod 755 system/plugin/ui/admin
```

### Step 3: Access Plugin Settings

1. Login to PHPNuxBill admin panel
2. Navigate to Settings → TumaSend
3. Configure your settings (see Configuration section)

### Step 4: Configure Cron Job (Optional)

If you want to use the queue system, add a cron job:

```bash
* * * * * php /path/to/your/phpnuxbill/system/cron_tumasend.php
```

## Configuration

### Required Settings

1. **Plugin Status**: Set to "Enabled"
2. **API Key**: Your TumaSend API key (get from TumaSend dashboard)
3. **Sender ID**: Your approved Sender ID (get from TumaSend dashboard)

### Optional Settings

- **API Base URL**: Default is `https://gateway.tumasend.com` (change only if using custom endpoint)
- **Connection Timeout**: Default 30 seconds
- **Retry Attempts**: Default 3 (how many times to retry failed requests)
- **Retry Delay**: Default 5 seconds (initial delay before retry)
- **Debug Logging**: Enable for troubleshooting
- **Webhook Secret**: Secret for webhook signature verification
- **Enable Webhooks**: Process delivery status updates
- **Enable Queue**: Background message processing

## Getting API Credentials

### 1. Create TumaSend Account

1. Visit https://tumasend.com
2. Sign up for an account
3. Verify your email address

### 2. Get API Key

1. Login to TumaSend dashboard
2. Navigate to Settings → API Keys
3. Create a new API key
4. Copy the key (starts with `ts_live_` for production, `ts_test_` for testing)

### 3. Configure Sender ID

1. In TumaSend dashboard, navigate to Settings → Sender IDs
2. Request a new Sender ID
3. Wait for approval (usually within 24 hours)
4. Copy the approved Sender ID

## Testing

### Send Test SMS

1. Go to Settings → TumaSend
2. Scroll to "Test SMS" section
3. Enter your phone number (in E.164 format: +265991234567)
4. Enter test message
5. Click "Send Test SMS"
6. Check if you receive the SMS

### Run Diagnostics

1. Go to Settings → TumaSend
2. Click "Run Diagnostics"
3. Review the diagnostic results
4. Fix any issues marked as "Fail" or "Warning"

## Troubleshooting

### Plugin Not Appearing in Menu

- Verify files are uploaded to correct locations
- Check file permissions
- Clear PHPNuxBill cache
- Refresh admin panel

### SMS Not Sending

- Check plugin is enabled
- Verify API key is correct
- Check sender ID is configured
- Run diagnostics
- Enable debug logging
- Check SMS history for error messages

### Webhook Not Working

- Verify webhook URL is accessible from internet
- Check webhook secret matches TumaSend configuration
- Enable webhooks in plugin settings
- Check firewall allows incoming connections
- Review webhook logs in SMS history

### Queue Not Processing

- Verify queue is enabled in settings
- Check cron job is configured correctly
- Verify cron job is running (check cron logs)
- Review queue statistics
- Check for failed items with error messages

## Upgrading

### From Previous Version

1. Backup current plugin files
2. Upload new plugin files
3. Access plugin settings
4. Run diagnostics to verify installation
5. Test with a test SMS

### Database Migrations

The plugin automatically creates/updates tables on initialization. No manual migration is required.

## Uninstallation

### Remove Plugin

1. Disable plugin in settings
2. Delete plugin files:
   ```bash
   rm -rf system/plugin/tumasend
   rm system/plugin/tumasend.php
   rm system/plugin/ui/admin/tumasend-*.tpl
   rm system/cron_tumasend.php
   ```
3. Remove cron job (if configured)
4. Optionally drop database tables:
   ```sql
   DROP TABLE IF EXISTS tbl_tumasend_config;
   DROP TABLE IF EXISTS tbl_tumasend_sms_history;
   DROP TABLE IF EXISTS tbl_tumasend_templates;
   DROP TABLE IF EXISTS tbl_tumasend_queue;
   ```

## Security Recommendations

1. **API Key Storage**: Consider encrypting API keys in database for production
2. **Webhook Secret**: Use a strong, random secret
3. **HTTPS**: Ensure your PHPNuxBill installation uses HTTPS
4. **Firewall**: Restrict access to webhook endpoint if needed
5. **Regular Updates**: Keep plugin updated to latest version
6. **Backup**: Regular backup of database tables

## Performance Optimization

1. **Queue System**: Enable queue for high-volume sending
2. **Cron Frequency**: Adjust cron frequency based on volume
3. **Batch Size**: Adjust queue processing limit in cron script
4. **Database Indexing**: Tables are automatically indexed
5. **Cleanup**: Regular cleanup of old queue items (automatic in cron)

## Support

For issues or questions:
- TumaSend Documentation: https://devs.tumasend.com/docs
- PHPNuxBill Community: https://t.me/ibnux
- Check plugin logs in PHPNuxBill message logs
