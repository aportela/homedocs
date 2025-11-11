<?php

declare(strict_types=1);

namespace HomeDocs;

class Settings
{
    public const array PHP_REQUIRED_EXTENSIONS = ['pdo_sqlite', 'mbstring'];
    /**
     * @var array<string,mixed>
     */
    private array $settings = [];

    public function __construct()
    {
        $settingsPath =  dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'settings.php';
        if (file_exists($settingsPath)) {
            $this->settings = require $settingsPath;
        } else {
            throw new \RuntimeException("Settings file not found: " . $settingsPath);
        }
    }

    public function getDefaultResultsPage(): int
    {
        if (is_array($this->settings["common"]) && is_numeric($this->settings["common"]["defaultResultsPage"])) {
            return (intval($this->settings["common"]["defaultResultsPage"]));
        } else {
            throw new \RuntimeException("Invalid settings key: common->defaultResultsPage");
        }
    }
}
