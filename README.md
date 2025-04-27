# Qbiez SMS Package for Laravel

![Version](https://img.shields.io/packagist/v/qsms/qbiez-sms)
![License](https://img.shields.io/packagist/l/qsms/qbiez-sms)
![PHP Version](https://img.shields.io/packagist/php-v/qsms/qbiez-sms)

A modern SMS integration package for Laravel applications with comprehensive features and robust error handling.

## 📋 Requirements

- PHP 8.0+
- Laravel 9.x/10.x/12.x
- Composer
- Active Qbiez SMS account

## 🚀 Quick Start

### Registration
First, register for a free account at [https://sms.qbiez.com/register](https://sms.qbiez.com/register) to obtain your API token and sender ID.

### Installation Steps

1. Install the package via Composer:
```bash
composer require qsms/qbiez-sms
```

2. Add the following variables to your `.env` file:
```env
QSMS_API_TOKEN=your_api_token_here
QSMS_SENDER_ID=your_sender_id_here
QSMS_API_URL=https://sms.qbiez.com/api/http/sms/send
QSMS_LOGGING_ENABLED=true
QSMS_DEFAULT_COUNTRY_CODE=255
QSMS_LOG_FORMAT=json
QSMS_LOG_RETENTION=14
QSMS_RATE_LIMIT=30
QSMS_RATE_WINDOW=60
```

### Basic Usage

```php
use Qsms\QbiezSms\SendSMS;

$sms = new SendSMS();
$response = $sms->send('255755270046', 'Hello World!');
```

## ⚙️ Advanced Configuration

To use advanced configuration options, follow these steps:

1. Create a new file `config/qsms.php` in your Laravel project
2. Copy the following configuration:

```php
return [
    'api_token' => env('QSMS_API_TOKEN', ''),
    'sender_id' => env('QSMS_SENDER_ID', ''),
    'api_url' => env('QSMS_API_URL', 'https://sms.qbiez.com/api/http/sms/send'),

    'http' => [
        'timeout' => env('QSMS_HTTP_TIMEOUT', 30),
        'retry' => [
            'times' => env('QSMS_RETRY_TIMES', 3),
            'sleep' => env('QSMS_RETRY_SLEEP', 100),
        ],
    ],

    'default_country_code' => env('QSMS_DEFAULT_COUNTRY_CODE', '255'),

    'logging' => [
        'enabled' => env('QSMS_LOGGING_ENABLED', true),
        'path' => storage_path('logs/qsms'),
        'level' => env('QSMS_LOG_LEVEL', 'info'),
    ],
];
```

3. Optionally, publish the configuration file (if you want to customize it further):
```bash
php artisan vendor:publish --tag=qsms-config
```

## 📱 Features

- Automatic phone number formatting
- Rate limiting protection
- Comprehensive logging
- Error handling
- Message splitting
- Queue support

### Phone Number Formats

```php
// All supported formats:
$sms->send('255712345678', 'Message');   // International
$sms->send('0712345678', 'Message');     // Local with zero
$sms->send('712345678', 'Message');      // Local without zero
$sms->send('+255712345678', 'Message');  // With plus
```

### Response Handling

```php
$response = $sms->send('255712345678', 'Test');

if ($response['status'] === 'success') {
    echo "Message sent! ID: " . $response['data']['message_id'];
} else {
    echo "Error: " . $response['message'];
}
```

## 🧪 Testing

```php
// tests/Feature/SmsTest.php
public function test_can_send_sms()
{
    $sms = new SendSMS();
    $response = $sms->send('255712345678', 'Test');
    $this->assertEquals('success', $response['status']);
}
```

## 📝 Logging

Logs are stored in `storage/logs/qsms/qsms-YYYY-MM-DD.log`

```json
{
    "timestamp": "2025-04-27 10:30:00",
    "level": "info",
    "message": "SMS sent",
    "context": {
        "recipient": "255712345678",
        "status": "delivered"
    }
}
```

## 🔒 Security

- Rate limiting: 10 messages/minute by default
- Secure API token handling
- Phone number validation
- Input sanitization

## 🤝 Support

- [Documentation](https://docs.qbiez.com)
- [GitHub Issues](https://github.com/qbiez/sms/issues)
- Email: support@qbiez.com

## 📄 License

MIT License. See [LICENSE](LICENSE) for details.

## 🔄 Changelog

See [CHANGELOG.md](CHANGELOG.md) for release history.
