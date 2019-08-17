<?php

    declare(strict_types=1);

    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

    echo "HomeDocs acount manager" . PHP_EOL;

    $app = (new \HomeDocs\App())->get();

    $missingExtensions = array_diff($app->getContainer()["settings"]["phpRequiredExtensions"], get_loaded_extensions());
    if (count($missingExtensions) > 0) {
        echo "Error: missing php extension/s: " . implode(", ", $missingExtensions) . PHP_EOL;
        exit;
    }

    $cmdLine = new \HomeDocs\CmdLine("", array("email:", "password:"));
    if ($cmdLine->hasParam("email") && $cmdLine->hasParam("password")) {
        $c = $app->getContainer();
        $c["logger"]->info("Setting account credentials");
        echo "Setting account credentials..." . PHP_EOL;
        $dbh = new \HomeDocs\Database\DB($c);
        if ((new \HomeDocs\Database\Version($dbh, $c->get("settings")['database']['type']))->hasUpgradeAvailable()) {
            $c["logger"]->warning("Process stopped: upgrade database before continue");
            echo "New database version available, an upgrade is required before continue." . PHP_EOL;
            exit;
        }
        $found = false;
        $u = new \HomeDocs\User("", $cmdLine->getParamValue("email"), $cmdLine->getParamValue("password"));
        $c["logger"]->debug("Email: " . $u->email . " / Password: " . $u->password);
        try {
            $u->get($dbh);
            $found = true;
        } catch (\HomeDocs\Exception\NotFoundException $e) { }
        if ($found) {
            $c["logger"]->debug("Account exists -> update credentials");
            echo "User found, updating password...";
            $u->update($dbh);
            echo "ok!" . PHP_EOL;
        } else {
            $c["logger"]->debug("Account not found -> adding credentials");
            echo "User not found, creating account...";
            $u = new \HomeDocs\User(\HomeDocs\Utils::uuidv4(), $cmdLine->getParamValue("email"), $cmdLine->getParamValue("password"));
            $u->add($dbh);
            echo "ok!" . PHP_EOL;
        }
    } else {
        echo "No required params found: --email <email> --password <secret>" . PHP_EOL;
    }

?>