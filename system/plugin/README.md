# TumaSend Communications Plugin for PHPNuxBill

A production-ready plugin integrating TumaSend's Communications API for SMS, WhatsApp, Email, and OTP services with PHPNuxBill billing system.

## Features

### Current Version (1.0.0)
- **SMS Integration**: Send single and bulk SMS messages via TumaSend API
- **Phone Number Normalization**: Automatic conversion to E.164 format (Malawi support)
- **Message Processing**: GSM-7/Unicode encoding detection and segment calculation
- **SMS History**: Complete audit trail of all sent messages
- **Message Templates**: Reusable templates with placeholder support
- **Webhook Support**: Delivery status updates via TumaSend webhooks
- **Queue System**: Background processing with retry logic
- **Diagnostics**: Built-in system health checks
- **Admin Interface**: Complete configuration and management UI

### Future Roadmap
- WhatsApp integration
- Email integration
- OTP services
- Redis queue backend
- Advanced analytics

## Installation

### Prerequisites
- PHPNuxBill installation
- PHP 7.4 or higher
- cURL extension
- JSON extension
- OpenSSL extension
- TumaSend API account

### Manual Installation

1. Copy the `tumasend` folder to `system/plugin/`
2. Copy the `tumasend.php` file to `system/plugin/`
3. Copy the admin templates to `system/plugin/ui/admin/`
4. Access the plugin via Admin → Settings → TumaSend

### Plugin Manager Installation

1. Download the plugin zip file
2. Go to Admin → Settings → Plugin Manager
3. Upload and install the plugin

## Configuration

### Basic Setup

1. **Enable Plugin**: Set "Plugin Status" to Enabled
2. **API Key**: Enter your TumaSend API key (starts with `ts_live_` or `ts_test_`)
3. **Sender ID**: Configure your approved Sender ID
4. **Save Settings**: Click "Save Settings"

### Advanced Configuration

- **API Base URL**: Default is `https://gateway.tumasend.com`
- **Connection Timeout**: Request timeout in seconds (default: 30)
- **Retry Attempts**: Maximum retry attempts for failed requests (default: 3)
- **Retry Delay**: Initial delay before retry with exponential backoff (default: 5)
- **Debug Logging**: Enable detailed logging for troubleshooting
- **Webhook Secret**: Secret for verifying webhook signatures
- **Enable Webhooks**: Process delivery status webhooks
- **Enable Queue**: Queue messages for background processing

## Usage

### Sending SMS via Plugin Hook

The plugin automatically intercepts SMS calls from PHPNuxBill when enabled:

```php
// This will be handled by TumaSend plugin if enabled
Message::sendSMS('+265991234567', 'Your message here');
```

### Direct API Usage

```php
use TumaSend\Plugin;

$plugin = new Plugin();
$sms = $plugin->getSMS();

// Send single SMS
$sms->send('+265991234567', 'Your message here');

// Send bulk SMS
$sms->sendBulk(['+265991234567', '+265881234567'], 'Bulk message');
```

### Using Templates

```php
$template = ORM::for_table('tbl_tumasend_templates')
    ->where('template_key', 'payment_confirmation')
    ->find_one();

$message = str_replace('{customer_name}', 'John Doe', $template->template_content);
$message = str_replace('{amount}', '5000 MWK', $message);

Message::sendSMS('+265991234567', $message);
```

### Queue Processing

Set up a cron job to process the queue:

```bash
* * * * * php /path/to/system/cron_tumasend.php
```

## Phone Number Formats

The plugin supports common Malawi phone number formats:

- `0991234567` → `+265991234567`
- `991234567` → `+265991234567`
- `265991234567` → `+265991234567`
- `+265991234567` → `+265991234567`

## Webhook Configuration

1. Configure your webhook URL in TumaSend dashboard
2. Set webhook URL to: `https://your-domain.com/?_route=plugin/tumasend/webhook`
3. Configure webhook secret in plugin settings
4. Enable webhooks in plugin settings

The plugin handles:
- `message.delivered` - Updates SMS status to delivered
- `message.failed` - Updates SMS status to failed with error details

## Database Tables

### tbl_tumasend_config
Plugin configuration storage

### tbl_tumasend_sms_history
SMS message history with delivery tracking

### tbl_tumasend_templates
Message templates with placeholder support

### tbl_tumasend_queue
Message queue for background processing

## Security

- API keys are stored in database (consider encryption for production)
- Webhook signatures are verified using HMAC SHA256
- All inputs are validated before API calls
- SQL injection protection via ORM
- XSS prevention via output escaping

## Troubleshooting

### SMS Not Sending

1. Check plugin is enabled in settings
2. Verify API key is correct
3. Check sender ID is configured
4. Run diagnostics to check system health
5. Enable debug logging for detailed errors

### Webhook Not Working

1. Verify webhook URL is accessible from TumaSend
2. Check webhook secret matches TumaSend configuration
3. Enable webhooks in plugin settings
4. Check logs for webhook events

### Queue Not Processing

1. Verify queue is enabled in settings
2. Check cron job is configured correctly
3. Review queue statistics in admin panel
4. Check for failed items with error messages

## API Reference

### TumaSend API Documentation

Official documentation: https://devs.tumasend.com/docs

### Supported Endpoints

- `POST /api/v1/send/sms` - Send SMS
- `GET /api/v1/balance` - Get account balance
- `GET /api/v1/batches` - List batches
- `GET /api/v1/batches/{id}` - Get batch details

## Support

For issues or questions:
- TumaSend Documentation: https://devs.tumasend.com/docs
- PHPNuxBill Community: https://t.me/ibnux
- TumaSend Support: Contact via TumaSend dashboard

## License

MIT License - See LICENSE file for details

## Credits

- TumaSend Communications API
- PHPNuxBill Billing System
- Plugin developed for production use

## Version History

### 1.0.0 (2024-07-19)
- Initial release
- SMS integration
- Phone number normalization
- Message templates
- Webhook support
- Queue system
- Admin interface
- Diagnostics
