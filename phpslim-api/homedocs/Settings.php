<?php

declare(strict_types=1);

namespace HomeDocs;

class Settings
{
    public const array PHP_REQUIRED_EXTENSIONS = ['pdo_sqlite', 'mbstring'];

    public const int DEFAULT_RESULTS_PAGE = 32;

    /**
     * @var array<string,mixed>
     */
    private array $settings = [];

    public function __construct()
    {
        $settingsPath =  dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'settings.php';
        if (file_exists($settingsPath)) {
            $this->settings = require $settingsPath;
            if ($this->getEnvironment() === 'development') {
                error_reporting(E_ALL);
                ini_set('display_errors', '1');
            } else {
                error_reporting(0);
                ini_set('display_errors', '0');
            }
            date_default_timezone_set($this->getDefaultTimezone());
        } else {
            throw new \RuntimeException("Settings file not found: " . $settingsPath);
        }
    }

    public function getEnvironment(): string
    {
        if (is_string($this->settings['environment'])) {
            return ($this->settings['environment']);
        } else {
            throw new \RuntimeException("Settings key (environment) not found");
        }
    }

    protected function getDefaultTimezone(): string
    {
        if (is_string($this->settings['defaultTimezone'])) {
            return ($this->settings['defaultTimezone']);
        } else {
            throw new \RuntimeException("Settings key (defaultTimezone) not found");
        }
    }

    public function allowSignUp(): bool
    {
        if (is_array($this->settings['common']) && is_bool($this->settings['common']['allowSignUp'])) {
            return ($this->settings['common']['allowSignUp']);
        } else {
            throw new \RuntimeException("Settings key (common->allowSignUp) not found");
        }
    }

    public function getJWTPassphrase(): string
    {
        if (is_array($this->settings['jwt']) && is_string($this->settings['jwt']['passphrase'])) {
            return ($this->settings['jwt']['passphrase']);
        } else {
            throw new \RuntimeException("Settings key (jwt->passphrase) not found");
        }
    }

    public function getStoragePath(): string
    {
        if (is_array($this->settings['paths']) && is_string($this->settings['paths']['storage'])) {
            return ($this->settings['paths']['storage']);
        } else {
            throw new \RuntimeException("Settings key (paths->storage) not found");
        }
    }
}
