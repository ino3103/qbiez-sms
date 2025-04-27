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

### Installation

```bash
composer require qsms/qbiez-sms
```

### Basic Configuration

Add to your `.env` file:

```env
QSMS_API_TOKEN=your_api_token_here
QSMS_SENDER_ID=your_sender_id_here
QSMS_API_URL=https://api.qbiez.com/v1/sms
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

```php
// config/qsms.php
return [
    'api_token' => env('QSMS_API_TOKEN'),
    'sender_id' => env('QSMS_SENDER_ID'),
    'api_url' => env('QSMS_API_URL'),
    
    'phone' => [
        'default_country_code' => '255',
        'allowed_countries' => ['255'],
        'min_length' => 9,
        'max_length' => 12
    ],
    
    'message' => [
        'max_length' => 160,
        'auto_split' => false,
        'encoding' => 'UTF-8'
    ]
];
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
