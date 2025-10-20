<?php

use DI\ContainerBuilder;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

$containerBuilder = new ContainerBuilder();

// Set up settings
$containerBuilder->addDefinitions(__DIR__ . '../../config/container.php');

// Build PHP-DI Container instance
$container = $containerBuilder->build();

echo "HomeDocs account manager" . PHP_EOL;

$logger = $container->get(\HomeDocs\Logger\InstallerLogger::class);

$settings = $container->get('settings');

$missingExtensions = array_diff($settings["phpRequiredExtensions"], get_loaded_extensions());
if (count($missingExtensions) > 0) {
    $missingExtensionsStr = implode(", ", $missingExtensions);
    echo "Error: missing php extension/s: " . $missingExtensionsStr . PHP_EOL;
    $logger->critical("Error: missing php extension/s: ", [$missingExtensionsStr]);
} else {
    $cmdLine = new \HomeDocs\CmdLine("", array("email:", "password:"));
    if ($cmdLine->hasParam("email") && $cmdLine->hasParam("password")) {
        echo "Setting account credentials..." . PHP_EOL;
        $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
        /*
        if ((new \HomeDocs\Database\Version($dbhh, "PDO_SQLITE"))->hasUpgradeAvailable()) {
            //$c["logger"]->warning("Process stopped: upgrade database before continue");
            echo "New database version available, an upgrade is required before continue." . PHP_EOL;
            exit;
        }
        */
        $found = false;
        $u = new \HomeDocs\User("", $cmdLine->getParamValue("email"), $cmdLine->getParamValue("password"));
        try {
            $u->get($dbh);
            $found = true;
        } catch (\HomeDocs\Exception\NotFoundException $e) {
        }
        if ($found) {
            //$c["logger"]->debug("Account exists -> update credentials");
            echo "User found, updating password...";
            $u->update($dbh);
            echo "ok!" . PHP_EOL;
        } else {
            //$c["logger"]->debug("Account not found -> adding credentials");
            echo "User not found, creating account...";
            $u->id = \HomeDocs\Utils::uuidv4();
            $u->add($dbh);
            echo "ok!" . PHP_EOL;
        }
    } else {
        echo "No required params found: --email <email> --password <secret>" . PHP_EOL;
    }
}
