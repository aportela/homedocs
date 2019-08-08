<?php

    declare(strict_types=1);

    namespace HomeDocs;

    class Utils {
        public static function getInitialState(\Slim\Container $container) {
            return(
                array(
                    'allowSignUp' => $container->get('settings')['common']['allowSignUp'],
                    'defaultResultsPage' => $container->get('settings')['common']['defaultResultsPage'],
                    'productionEnvironment' => $container->get('settings')['twigParams']['production'],
                    'session' => array(
                        'logged' => \HomeDocs\UserSession::isLogged(),
                        'id' => \HomeDocs\UserSession::getUserId(),
                        'email' => \HomeDocs\UserSession::getEmail()
                    )
                )
            );
        }
    }
?>