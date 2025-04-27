<?php

namespace Qsms\QbiezSms;

use Illuminate\Support\Facades\Http;
use Exception;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;

class SendSMS
{
    protected $apiToken;
    protected $senderId;
    protected $apiUrl;
    protected $logger;
    protected $logPath;
    protected $defaultCountryCode;
    protected $allowedCountries;
    protected $phoneNumberLengths;
    protected $messageSettings;
    protected $securitySettings;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->validateConfig();

        $this->apiToken = config('qsms.api_token');
        $this->senderId = config('qsms.sender_id');
        $this->apiUrl = config('qsms.api_url');

        // Phone number handling
        $this->defaultCountryCode = config('qsms.phone.default_country_code', '255');
        $this->allowedCountries = config('qsms.phone.allowed_countries', ['255']);
        $this->phoneNumberLengths = [
            'min' => config('qsms.phone.min_length', 9),
            'max' => config('qsms.phone.max_length', 12)
        ];

        // Message settings
        $this->messageSettings = [
            'max_length' => config('qsms.message.max_length', 160),
            'auto_split' => config('qsms.message.auto_split', false),
            'encoding' => config('qsms.message.default_encoding', 'UTF-8')
        ];

        // Security settings
        $this->securitySettings = [
            'rate_limit' => config('qsms.security.rate_limit', 10),
            'rate_limit_window' => config('qsms.security.rate_limit_window', 60)
        ];

        $this->initializeLogging();
        $this->logger = $logger ?? Log::channel('qsms');
    }

    protected function validateConfig(): void
    {
        foreach (['api_token', 'sender_id', 'api_url'] as $key) {
            if (empty(config('qsms.' . $key))) {
                throw new \RuntimeException("QSMS configuration missing: $key");
            }
        }
    }

    protected function initializeLogging()
    {
        $this->logPath = config('qsms.logging.path', storage_path('logs/qsms'));

        if (!file_exists($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }

        if (is_null(config('logging.channels.qsms'))) {
            config([
                'logging.channels.qsms' => [
                    'driver' => config('qsms.logging.driver', 'daily'),
                    'path' => $this->logPath . '/qsms.log',
                    'level' => config('qsms.logging.level', 'info'),
                    'days' => config('qsms.logging.days', 14),
                    'permission' => 0755,
                    'formatter' => config('qsms.logging.format') === 'json'
                        ? \Monolog\Formatter\JsonFormatter::class
                        : null,
                ]
            ]);
        }
    }

    public function send(string $to, string $message): array
    {
        $this->checkRateLimit();

        $startTime = microtime(true);
        $logData = [
            'to_original' => $to,
            'message' => $this->truncateMessage($message),
            'sender_id' => $this->senderId,
            'timestamp' => now()->toDateTimeString(),
        ];

        try {
            // Validate and format phone number
            $to = $this->validateAndFormatPhoneNumber($to);
            $logData['to_formatted'] = $to;

            if (empty($message)) {
                return $this->handleError($logData, 'Message content is required');
            }

            // Validate message length
            $messages = $this->prepareMessage($message);
            if (count($messages) > 1) {
                $logData['split_messages'] = count($messages);
            }

            $this->logger->info('Sending SMS attempt', $logData);

            $responses = [];
            foreach ($messages as $msg) {
                $response = Http::timeout(config('qsms.http.timeout', 30))
                    ->connectTimeout(config('qsms.http.connect_timeout', 10))
                    ->retry(
                        config('qsms.http.retry.times', 3),
                        config('qsms.http.retry.sleep', 100)
                    )
                    ->post($this->apiUrl, [
                        'api_token' => $this->apiToken,
                        'sender_id' => $this->senderId,
                        'recipient' => $to,
                        'message' => $msg,
                        'encoding' => $this->messageSettings['encoding']
                    ]);

                $responses[] = $response->json();
            }

            $logData['response'] = $responses;
            $logData['duration_ms'] = round((microtime(true) - $startTime) * 1000, 2);

            return $this->handleApiResponse($logData, $responses);
        } catch (Exception $e) {
            return $this->handleException($logData, $e);
        }
    }

    protected function prepareMessage(string $message): array
    {
        if (
            !$this->messageSettings['auto_split'] &&
            strlen($message) > $this->messageSettings['max_length']
        ) {
            throw new \InvalidArgumentException(
                "Message exceeds maximum length of {$this->messageSettings['max_length']} characters"
            );
        }

        if ($this->messageSettings['auto_split']) {
            return str_split(
                $message,
                $this->messageSettings['max_length']
            );
        }

        return [$message];
    }

    protected function validateAndFormatPhoneNumber(string $number): string
    {
        // Remove all non-digit characters
        $cleanNumber = preg_replace('/[^0-9]/', '', $number);

        // Handle different number formats
        if (str_starts_with($number, '+')) {
            $formatted = substr($cleanNumber, 0); // +255 -> 255...
        } elseif (str_starts_with($cleanNumber, '0')) {
            $formatted = $this->defaultCountryCode . substr($cleanNumber, 1); // 07... -> 2557...
        } elseif (strlen($cleanNumber) === 9 && !str_starts_with($cleanNumber, '0')) {
            $formatted = $this->defaultCountryCode . $cleanNumber; // 7... -> 2557...
        } elseif (str_starts_with($cleanNumber, $this->defaultCountryCode)) {
            $formatted = $cleanNumber; // Already in international format
        } else {
            throw new \InvalidArgumentException("Invalid phone number format: $number");
        }

        // Validate country code
        $countryCode = substr($formatted, 0, 3);
        if (!in_array($countryCode, $this->allowedCountries)) {
            throw new \InvalidArgumentException(
                "Phone number from country {$countryCode} is not allowed"
            );
        }

        // Validate length
        $numberLength = strlen($formatted);
        if (
            $numberLength < $this->phoneNumberLengths['min'] ||
            $numberLength > $this->phoneNumberLengths['max']
        ) {
            throw new \InvalidArgumentException(
                "Phone number must be between {$this->phoneNumberLengths['min']} " .
                    "and {$this->phoneNumberLengths['max']} digits"
            );
        }

        return $formatted;
    }

    protected function checkRateLimit(): void
    {
        $logFiles = glob($this->logPath . '/qsms*.log');
        $recentMessages = 0;
        $windowStart = time() - $this->securitySettings['rate_limit_window'];

        foreach ($logFiles as $file) {
            if (filemtime($file) >= $windowStart) {
                $content = file_get_contents($file);
                $recentMessages += substr_count($content, 'SMS delivered successfully');
            }
        }

        if ($recentMessages >= $this->securitySettings['rate_limit']) {
            throw new \RuntimeException(
                "Rate limit exceeded. Max {$this->securitySettings['rate_limit']} " .
                    "messages per {$this->securitySettings['rate_limit_window']} seconds"
            );
        }
    }

    protected function handleApiResponse(array $logData, array $responses): array
    {
        $primaryResponse = end($responses);

        if ($primaryResponse['status'] === 'success') {
            $this->logger->info('SMS delivered successfully', $logData);
            return [
                'status' => 'success',
                'message' => $primaryResponse['message'] ?? 'Message delivered',
                'data' => $primaryResponse['data'] ?? [],
                'sms_id' => $primaryResponse['data']['uid'] ?? null,
                'cost' => $primaryResponse['data']['cost'] ?? null,
                'sms_count' => $primaryResponse['data']['sms_count'] ?? count($responses),
                'split_messages' => count($responses) > 1 ? count($responses) : null
            ];
        }

        $this->logger->error('SMS delivery failed', $logData);
        return [
            'status' => 'error',
            'message' => $primaryResponse['message'] ?? 'SMS sending failed',
            'data' => $primaryResponse['data'] ?? [],
            'response' => $responses
        ];
    }

    protected function handleError(array $logData, string $error): array
    {
        $this->logger->error($error, $logData);
        return [
            'status' => 'error',
            'message' => $error
        ];
    }

    protected function handleException(array $logData, Exception $e): array
    {
        $logData['error'] = $e->getMessage();
        $this->logger->error('SMS sending exception', $logData);

        return [
            'status' => 'error',
            'message' => $e->getMessage(),
            'exception' => get_class($e)
        ];
    }

    protected function truncateMessage(string $message, int $length = 100): string
    {
        return strlen($message) <= $length ? $message : substr($message, 0, $length) . '...';
    }

    public function getLogPath(): string
    {
        return $this->logPath;
    }
}
