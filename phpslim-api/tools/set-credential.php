<?php

use DI\ContainerBuilder;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

$containerBuilder = new ContainerBuilder();

// Set up settings
$containerBuilder->addDefinitions(__DIR__ . '../../config/container.php');

// Build PHP-DI Container instance
$container = $containerBuilder->build();

echo "[-] HomeDocs account manager" . PHP_EOL;

$logger = $container->get(\HomeDocs\Logger\InstallerLogger::class);

$settings = $container->get('settings');

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
$cmdLine = new \HomeDocs\CmdLine("", ["email:", "password:"]);
if ($cmdLine->hasParam("email") && $cmdLine->hasParam("password")) {
    $email = $cmdLine->getParamValue("email");
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo ' error! - invalid email param: ' . $email . PHP_EOL;
        $logger->error("Invalid email param", [$email]);
        exit(1);
    } else {
        echo " success!" . PHP_EOL;
    }
    
    echo "[?] Setting account credentials...";
    $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
    $found = false;
    $u = new \HomeDocs\User("", $cmdLine->getParamValue("email"), $cmdLine->getParamValue("password"));
    try {
        $u->get($dbh);
        $found = true;
    } catch (\HomeDocs\Exception\NotFoundException) {
    }

    if ($found) {
        //$c["logger"]->debug("Account exists -> update credentials");
        echo " user found, updating password...";
        $u->update($dbh);
        echo " success!" . PHP_EOL;
    } else {
        //$c["logger"]->debug("Account not found -> adding credentials");
        echo " user not found, creating account...";
        $u->id = \HomeDocs\Utils::uuidv4();
        $u->add($dbh);
        echo " success!" . PHP_EOL;
    }
    
    exit(0);
} else {
    echo " error! - No required params found: --email <email> --password <secret>" . PHP_EOL;
    exit(1);
}
