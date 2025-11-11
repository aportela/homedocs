<?php

$currentPath = dirname(__DIR__);

$environment = 'development'; // (development|production)

$logPath = $currentPath . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'logs';

return [
    'environment' => $environment,
    'defaultTimezone' => 'Europe/Madrid',
    'common' => [
        'allowSignUp' => true
    ],
    'jwt' => [
        // WARNING: for security reasons, generate a random string for using as your OWN (not default) passphrase
        'passphrase' => '/@q]/?pc`c&bq,P/MCp{5#E~-Nr2]NXQ$pvSKiz$tLQd]K)>eIOOk!&6rKVO7J~'
    ],
    'paths' => [
        'storage' => $currentPath . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'storage',
        'logs' => $logPath
    ],
    'db' => [
        'driver' => 'sqlite',
        'host' => '',
        'username' => '',
        'database' => $currentPath . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'homedocs3.sqlite3',
        'password' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'flags' => [
            // Turn off persistent connections
            PDO::ATTR_PERSISTENT => false,
            // Enable exceptions
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // Emulate prepared statements
            PDO::ATTR_EMULATE_PREPARES => true,
            // Set default fetch mode to array
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Set character set
            //PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci' // BUG: https://bugs.php.net/bug.php?id=81576
        ],
        'upgradeSchemaPath' => $currentPath . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'db-schema.php'
    ],
    // Error Handling Middleware settings
    'error' => [

        // Should be set to false in production
        /** @phpstan-ignore-next-line */
        'display_error_details' => $environment === 'development',

        // Parameter is passed to the default ErrorHandler
        // View in rendered output by enabling the 'displayErrorDetails' setting.
        // For the console and unit tests we also disable it
        'log_errors' => true,

        // Display error details in error log
        'log_error_details' => true,
    ],
    'logger' => [
        /** @phpstan-ignore-next-line */
        'defaultLevel' => $environment === 'development' ? \Monolog\Level::Debug : \Monolog\Level::Error,
        'channels' => [
            'default'  => [
                'path' => isset($_ENV['docker']) ? 'php://stdout' : $logPath . DIRECTORY_SEPARATOR . 'default.log',
                'name' => 'Homedocs::Default'
            ],
            'http'  => [
                'path' => isset($_ENV['docker']) ? 'php://stdout' : $logPath . DIRECTORY_SEPARATOR . 'http.log',
                'name' => 'Homedocs::HTTP'
            ],
            'installer' => [
                'path' => isset($_ENV['docker']) ? 'php://stdout' : $logPath . DIRECTORY_SEPARATOR . 'installer.log',
                'name' => 'Homedocs::Installer'
            ],
            'database' => [
                'path' => isset($_ENV['docker']) ? 'php://stdout' : $logPath . DIRECTORY_SEPARATOR . 'database.log',
                'name' => 'Homedocs::Database'
            ]
        ]
    ]
];
