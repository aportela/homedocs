<?php

    declare(strict_types=1);

    namespace HomeDocs;

    return [
        'settings' => [
            'displayErrorDetails' => true, // disable on production environments
            'phpRequiredExtensions' => array('pdo_mysql', 'mbstring'),
            'twigParams' => [
                'production' => false,
                'localVendorAssets' => true // use local vendor assets (vs remote cdn)
            ],
            // Renderer settings
            'renderer' => [
                'template_path' => __DIR__ . '/../templates',
            ],
            // database settings
            'database' => [
                'type' => "PDO_MARIADB", // supported types: PDO_SQLITE
                'name' => 'homedocs',
                'username' => '',
                'password' => '',
                'host' => 'localhost',
                'port' => 3306,
            ],
            // Monolog settings
            'logger' => [
                'name' => 'homedocs-app',
                'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/default.log',
                'level' => \Monolog\Logger::DEBUG
            ],
            'databaseLogger' => [
                'name' => 'homedocs-db',
                'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/database.log',
                'level' => \Monolog\Logger::DEBUG
            ],
            'apiLogger' => [
                'name' => 'homedocs-api',
                'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/api.log',
                'level' => \Monolog\Logger::DEBUG
            ],
            'common' => [
                'allowSignUp' => true, // allow user public sign-ups
                'defaultResultsPage' => 32 // default pagination results
            ]
        ],
    ];

?>