<?php

use DI\ContainerBuilder;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$containerBuilder = new ContainerBuilder();

// Set up container
$containerBuilder->addDefinitions(__DIR__ . '../../config/container.php');

// Build PHP-DI Container instance
$container = $containerBuilder->build();

echo "[-] HomeDocs random data generator " . PHP_EOL;

$logger = $container->get(\HomeDocs\Logger\InstallerLogger::class);
if (! $logger instanceof \HomeDocs\Logger\InstallerLogger) {
    echo "[E] Error getting logger from container" . PHP_EOL;
    exit(1);
}

$installer = new \HomeDocs\Installer($logger, $container);

echo "[?] Checking php required extensions...";
if ($installer->checkRequiredPHPExtensions()) {
    echo " success!" . PHP_EOL;
} else {
    $missingPHPExtensions = $installer->getMissingPHPExtensions();
    echo " error! - missing extensions: " . implode(",", $missingPHPExtensions) . PHP_EOL;
    $logger->error("Missing php required extensions", $missingPHPExtensions);
    exit(1);
}


echo "[?] Checking params...";
$cmdLine = new \HomeDocs\CmdLine("", ["count:", "email:", "password:"]);
if ($cmdLine->hasParam("count") && $cmdLine->hasParam("email") && $cmdLine->hasParam("password")) {
    $email = $cmdLine->getParamValue("email");
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo ' error! - invalid email param: ' . $email . PHP_EOL;
        $logger->error("Invalid email param", [$email]);
        exit(1);
    } else {
        echo " success!" . PHP_EOL;
    }

    $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
    if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
        echo "[E] Error getting database handler from container" . PHP_EOL;
        $logger->error("Error getting database handler from container");
        exit(1);
    }

    $u = new \HomeDocs\User("", $cmdLine->getParamValue("email"), $cmdLine->getParamValue("password"));
    $found = false;
    try {
        $u->get($dbh);
        $found = true;
    } catch (\HomeDocs\Exception\NotFoundException) {
    }

    if ($found) {
        //$c["logger"]->debug("Account exists -> update credentials");
        echo "[I] User found, updating password...";
        $u->update($dbh);
        echo " success!" . PHP_EOL;
    } else {
        //$c["logger"]->debug("Account not found -> adding credentials");
        echo "[I] User not found, creating account...";
        $u->id = \HomeDocs\Utils::uuidv4();
        $u->add($dbh);
        echo " success!" . PHP_EOL;
    }

    $totalMocks = $cmdLine->getParamValue("count");
    echo sprintf("[I] Inserting %d mocks: ", $totalMocks);
    for ($i = 0; $i < $totalMocks; ++$i) {
        $mock = new \HomeDocs\Mock($u->id, 3, 5);
        $queries = $mock->getQueries();
        $dbh->beginTransaction();
        foreach ($queries as $query) {
            $dbh->exec($query);
        }

        $dbh->commit();
        echo ".";
    }

    echo " success!" . PHP_EOL;
    exit(0);
} else {
    echo " error! - No required params found: --count <number> --email <email> --password <secret>" . PHP_EOL;
    exit(1);
}
