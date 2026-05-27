<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;

return [

    'default' => env('LOG_CHANNEL', 'stack'),

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => false,
    ],

    'channels' => [

        'stack' => [
            'driver' => 'stack',
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'replace_placeholders' => true,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => env('LOG_LEVEL', 'critical'),
            'replace_placeholders' => true,
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://' . env('PAPERTRAIL_URL') . ':' . env('PAPERTRAIL_PORT'),
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
            'facility' => LOG_USER,
            'replace_placeholders' => true,
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],

        // ----------------------------
        // 🔥 YOUR CUSTOM CHANNELS HERE
        // ----------------------------

        'getsender' => [
            'driver' => 'single',
            'path' => storage_path('logs/getsender.log'),
            'level' => 'info',
        ],

        'beneficiary' => [
            'driver' => 'single',
            'path' => storage_path('logs/beneficiary.log'),
            'level' => 'info',
        ],

        'accountverify' => [
            'driver' => 'single',
            'path' => storage_path('logs/accountverify.log'),
            'level' => 'info',
        ],
          'IPCallBack' => [
            'driver' => 'single',
            'path' => storage_path('logs/IPCallBack.log'),
            'level' => 'info',
        ],

        'fundtransfer' => [
            'driver' => 'single',
            'path' => storage_path('logs/fundtransfer.log'),
            'level' => 'info',
        ],

        'payoutstatus' => [
            'driver' => 'single',
            'path' => storage_path('logs/payoutstatus.log'),
            'level' => 'info',
        ],

        'webhook' => [
            'driver' => 'single',
            'path' => storage_path('logs/webhook.log'),
            'level' => 'info',
        ],

            'PaydrionImpsService' => [
                'driver' => 'single',
                'path' => storage_path('logs/paydrion-imps-service.log'),
                'level' => 'debug',
            ],

            'PaydrionUpiService' => [
                'driver' => 'single',
                'path' => storage_path('logs/paydrion-upi-service.log'),
                'level' => 'debug',
            ],

             'PaydrionPayInService' => [
                'driver' => 'single',
                'path' => storage_path('logs/paydrion-upi-service.log'),
                'level' => 'debug',
            ],
             'PaydrionImps' => [
                'driver' => 'single',
                'path' => storage_path('logs/paydrion-imps.log'),
                'level' => 'debug',
            ],

            'PaydrionUpi' => [
                'driver' => 'single',
                'path' => storage_path('logs/paydrion-upi.log'),
                'level' => 'debug',
            ],
            
            'PaydrionPayin' => [
                'driver' => 'single',
                'path' => storage_path('logs/paydrion-upi.log'),
                'level' => 'debug',
            ],

            'PaydrionCallback' => [
                'driver' => 'single',
                'path' => storage_path('logs/paydrion-callback.log'),
                'level' => 'debug',
            ],
    ],

];
