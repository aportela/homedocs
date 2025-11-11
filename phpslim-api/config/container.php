<?php

use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Middleware\ErrorMiddleware;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

return [
    'settings' => fn() => require __DIR__ . DIRECTORY_SEPARATOR . 'settings.php',

    App::class => function (ContainerInterface $container): \Slim\App {
        AppFactory::setContainer($container);
        return AppFactory::create();
    },

    ErrorMiddleware::class => function (ContainerInterface $container): \Slim\Middleware\ErrorMiddleware {
        $app = $container->get(App::class);
        if (! $app instanceof \Slim\App) {
            throw new \RuntimeException("Failed to get Application (App) from container");
        }
        $settings = new \HomeDocs\Settings();
        return new ErrorMiddleware(
            $app->getCallableResolver(),
            $app->getResponseFactory(),
            $settings->getErrorBooleanKey('display_error_details'),
            $settings->getErrorBooleanKey('log_errors'),
            $settings->getErrorBooleanKey('log_error_details')
        );
    },

    \aportela\DatabaseWrapper\DB::class => function (ContainerInterface $container): \aportela\DatabaseWrapper\DB {
        $dbSettings = new \HomeDocs\Settings();
        $adapter = new \aportela\DatabaseWrapper\Adapter\PDOSQLiteAdapter(
            $dbSettings->getDatabasePath(),
            $dbSettings->getDatabasePDOOptions(),
            \aportela\DatabaseWrapper\Adapter\PDOSQLiteAdapter::FLAGS_PRAGMA_JOURNAL_WAL | \aportela\DatabaseWrapper\Adapter\PDOSQLiteAdapter::FLAGS_PRAGMA_FOREIGN_KEYS_ON,
            // READ upgrade SQL schema file definition on next block of this README.md
            $dbSettings->getDatabaseUpgradeSchemaPath()
        );
        $logger = $container->get(\HomeDocs\Logger\DBLogger::class);
        if (! $logger instanceof \HomeDocs\Logger\DBLogger) {
            throw new \RuntimeException("Failed to get logger (DBLogger) from container");
        }
        // main object
        $db = new \aportela\DatabaseWrapper\DB(
            $adapter,
            $logger
        );
        return ($db);
    },

    \HomeDocs\Logger\HTTPRequestLogger::class => function (ContainerInterface $container): \HomeDocs\Logger\HTTPRequestLogger {
        $settings = new \HomeDocs\Settings();

        $logger = new \Monolog\Logger($settings->getLoggerChannelProperty("http", "name"));
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());

        $handler = new \Monolog\Handler\RotatingFileHandler($settings->getLoggerChannelProperty("http", "path"), 0, $settings->getLoggerDefaultLevel());
        $handler->setFilenameFormat('{date}/{filename}', \Monolog\Handler\RotatingFileHandler::FILE_PER_DAY);
        //$formatter = new \Monolog\Formatter\LineFormatter(null, null, true, true);
        //$handler->setFormatter($formatter);
        $logger->pushHandler($handler);
        return (new \HomeDocs\Logger\HTTPRequestLogger($logger));
    },

    \HomeDocs\Logger\DefaultLogger::class => function (ContainerInterface $container): \HomeDocs\Logger\DefaultLogger {
        $settings = new \HomeDocs\Settings();

        $logger = new \Monolog\Logger($settings->getLoggerChannelProperty("default", "name"));
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());

        $handler = new \Monolog\Handler\RotatingFileHandler($settings->getLoggerChannelProperty("default", "path"), 0, $settings->getLoggerDefaultLevel());
        $handler->setFilenameFormat('{date}/{filename}', \Monolog\Handler\RotatingFileHandler::FILE_PER_DAY);
        //$formatter = new \Monolog\Formatter\LineFormatter(null, null, true, true);
        //$handler->setFormatter($formatter);
        $logger->pushHandler($handler);
        return (new \HomeDocs\Logger\DefaultLogger($logger));
    },

    \HomeDocs\Logger\DBLogger::class => function (ContainerInterface $container): \HomeDocs\Logger\DBLogger {
        $settings = new \HomeDocs\Settings();

        $logger = new \Monolog\Logger($settings->getLoggerChannelProperty("database", "name"));
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());

        $handler = new \Monolog\Handler\RotatingFileHandler($settings->getLoggerChannelProperty("database", "path"), 0, $settings->getLoggerDefaultLevel());
        $handler->setFilenameFormat('{date}/{filename}', \Monolog\Handler\RotatingFileHandler::FILE_PER_DAY);
        //$formatter = new \Monolog\Formatter\LineFormatter(null, null, true, true);
        //$handler->setFormatter($formatter);
        $logger->pushHandler($handler);
        return (new \HomeDocs\Logger\DBLogger($logger));
    },

    \HomeDocs\Logger\InstallerLogger::class => function (ContainerInterface $container): \HomeDocs\Logger\InstallerLogger {
        $settings = new \HomeDocs\Settings();

        $logger = new \Monolog\Logger($settings->getLoggerChannelProperty("installer", "name"));
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());

        $handler = new \Monolog\Handler\RotatingFileHandler($settings->getLoggerChannelProperty("installer", "path"), 0, $settings->getLoggerDefaultLevel());
        $handler->setFilenameFormat('{date}/{filename}', \Monolog\Handler\RotatingFileHandler::FILE_PER_DAY);
        //$formatter = new \Monolog\Formatter\LineFormatter(null, null, true, true);
        //$handler->setFormatter($formatter);
        $logger->pushHandler($handler);
        return (new \HomeDocs\Logger\InstallerLogger($logger));
    },

    \HomeDocs\Middleware\APIExceptionCatcher::class => function (ContainerInterface $container) {
        $logger = $container->get(\HomeDocs\Logger\HTTPRequestLogger::class);
        if (! $logger instanceof \HomeDocs\Logger\HTTPRequestLogger) {
            throw new \RuntimeException("Failed to get logger (HTTPRequestLogger) from container");
        }
        return (new \HomeDocs\Middleware\APIExceptionCatcher($logger));
    }
];
