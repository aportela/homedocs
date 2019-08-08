<?php

    declare(strict_types=1);

    ob_start();

    require __DIR__ . '/../vendor/autoload.php';

    session_name("HOMEDOCSPHPSESSID");
    session_start();

    $app = (new \HomeDocs\App())->get();

    $app->run();

?>