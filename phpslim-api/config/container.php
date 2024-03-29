<?php

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Middleware\ErrorMiddleware;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

return [
    'settings' => function () {
        return require __DIR__ . DIRECTORY_SEPARATOR . 'settings.php';
    },

    App::class => function (ContainerInterface $container) {
        AppFactory::setContainer($container);
        return AppFactory::create();
    },

    ResponseFactoryInterface::class => function (ContainerInterface $container) {
        return $container->get(Psr17Factory::class);
    },

    ErrorMiddleware::class => function (ContainerInterface $container) {
        $app = $container->get(App::class);
        $settings = $container->get('settings')['error'];
        return new ErrorMiddleware(
            $app->getCallableResolver(),
            $app->getResponseFactory(),
            (bool)$settings['display_error_details'],
            (bool)$settings['log_errors'],
            (bool)$settings['log_error_details']
        );
    },

    Twig::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['twig'];
        $twig = \Slim\Views\Twig::create($settings['path'], $settings['options']);
        return $twig;
    },

    \aportela\DatabaseWrapper\DB::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['db'];
        $adapter = new \aportela\DatabaseWrapper\Adapter\PDOSQLiteAdapter(
            $settings["database"],
            // READ upgrade SQL schema file definition on next block of this README.md
            $settings["upgradeSchemaPath"]
        );
        $logger = $container->get(\HomeDocs\Logger\DBLogger::class);
        // main object
        $db = new \aportela\DatabaseWrapper\DB(
            $adapter,
            $logger
        );
        return ($db);
    },

    \HomeDocs\Logger\HTTPRequestLogger::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['logger'];
        $logger = new \HomeDocs\Logger\HTTPRequestLogger($settings['channels']['http']['name']);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $handler = new \Monolog\Handler\RotatingFileHandler($settings['channels']['http']['path'], 0, $settings['defaultLevel']);
        $handler->setFilenameFormat('{date}/{filename}', \Monolog\Handler\RotatingFileHandler::FILE_PER_DAY);
        $formatter = new \Monolog\Formatter\LineFormatter(null, null, true, true);
        //$handler->setFormatter($formatter);
        $logger->pushHandler($handler);
        return ($logger);
    },

    \HomeDocs\Logger\DefaultLogger::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['logger'];
        $logger = new \HomeDocs\Logger\DefaultLogger($settings['channels']['default']['name']);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $handler = new \Monolog\Handler\RotatingFileHandler($settings['channels']['default']['path'], 0, $settings['defaultLevel']);
        $handler->setFilenameFormat('{date}/{filename}', \Monolog\Handler\RotatingFileHandler::FILE_PER_DAY);
        $formatter = new \Monolog\Formatter\LineFormatter(null, null, true, true);
        //$handler->setFormatter($formatter);
        $logger->pushHandler($handler);
        return ($logger);
    },

    \HomeDocs\Logger\DBLogger::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['logger'];
        $logger = new \HomeDocs\Logger\DBLogger($settings['channels']['database']['name']);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $handler = new \Monolog\Handler\RotatingFileHandler($settings['channels']['database']['path'], 0, $settings['defaultLevel']);
        $handler->setFilenameFormat('{date}/{filename}', \Monolog\Handler\RotatingFileHandler::FILE_PER_DAY);
        $formatter = new \Monolog\Formatter\LineFormatter(null, null, true, true);
        //$handler->setFormatter($formatter);
        $logger->pushHandler($handler);
        return ($logger);
    },

    \HomeDocs\Logger\InstallerLogger::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['logger'];
        $logger = new \HomeDocs\Logger\InstallerLogger($settings['channels']['installer']['name']);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $handler = new \Monolog\Handler\RotatingFileHandler($settings['channels']['installer']['path'], 0, $settings['defaultLevel']);
        $handler->setFilenameFormat('{date}/{filename}', \Monolog\Handler\RotatingFileHandler::FILE_PER_DAY);
        $formatter = new \Monolog\Formatter\LineFormatter(null, null, true, true);
        //$handler->setFormatter($formatter);
        $logger->pushHandler($handler);
        return ($logger);
    },

    \HomeDocs\Middleware\APIExceptionCatcher::class => function (ContainerInterface $container) {
        return (new \HomeDocs\Middleware\APIExceptionCatcher($container->get(\HomeDocs\Logger\HTTPRequestLogger::class)));
    }
];
