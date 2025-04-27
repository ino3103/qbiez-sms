<?php

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
