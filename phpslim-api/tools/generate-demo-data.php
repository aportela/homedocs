<?php

use DI\ContainerBuilder;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$containerBuilder = new ContainerBuilder();

// Set up settings
$containerBuilder->addDefinitions(__DIR__ . '../../config/container.php');

// Build PHP-DI Container instance
$container = $containerBuilder->build();

echo "HomeDocs random data generator " . PHP_EOL;

$logger = $container->get(\HomeDocs\Logger\InstallerLogger::class);

$logger->info("Mock started");

$settings = $container->get('settings');

$missingExtensions = array_diff($settings["phpRequiredExtensions"], get_loaded_extensions());
if (count($missingExtensions) > 0) {
    $missingExtensionsStr = implode(", ", $missingExtensions);
    echo "Error: missing php extension/s: " . $missingExtensionsStr . PHP_EOL;
    $logger->critical("Error: missing php extension/s: ", [$missingExtensionsStr]);
} else {
    $cmdLine = new \HomeDocs\CmdLine("", array("count:", "email:", "password:"));
    if ($cmdLine->hasParam("count") && $cmdLine->hasParam("email") && $cmdLine->hasParam("password")) {
        $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
        $u = new \HomeDocs\User("", $cmdLine->getParamValue("email"), $cmdLine->getParamValue("password"));

        try {
            $u->get($dbh);
            $found = true;
        } catch (\HomeDocs\Exception\NotFoundException $e) {
        }

        if ($found) {
            //$c["logger"]->debug("Account exists -> update credentials");
            echo "User found, updating password...";
            $u->update($dbh, false);
            echo "ok!" . PHP_EOL;
        } else {
            //$c["logger"]->debug("Account not found -> adding credentials");
            echo "User not found, creating account...";
            $u->id = \HomeDocs\Utils::uuidv4();
            $u->add($dbh);
            echo "ok!" . PHP_EOL;
        }
        $totalMocks = $cmdLine->getParamValue("count");
        echo sprintf("Inserting %d mocks: ", $totalMocks);
        for ($i = 0; $i < $totalMocks; $i++) {
            $mock = new \HomeDocs\Mock($u->id, 3, 5);
            $queries = $mock->getQueries();
            $dbh->beginTransaction();
            foreach ($queries as $query) {
                $dbh->exec($query);
            }
            $dbh->commit();
            echo ".";
        }
        echo "ok!" . PHP_EOL;
    } else {
        echo "No required params found: --count <number> --email <email> --password <secret>" . PHP_EOL;
    }
}
