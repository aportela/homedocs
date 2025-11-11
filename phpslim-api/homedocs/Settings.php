<?php

declare(strict_types=1);

namespace HomeDocs;

class Settings
{
    /**
     * @var array<string,mixed>
     */
    private array $settings = [];

    public function __construct(private readonly \Psr\Log\LoggerInterface $logger, \Psr\Container\ContainerInterface $container)
    {
        $settings = $container->get("settings");
        if (is_array($settings)) {
            $this->settings = $settings;
        } else {
            throw new \RuntimeException("Failed to get settings from container");
        }
    }

    /**
     * @return array<string>
     */
    public function getPHPRequiredExtensions(): array
    {
        if (is_array($this->settings["phpRequiredExtensions"])) {
            return ($this->settings["phpRequiredExtensions"]);
        } else {
            throw new \RuntimeException("Invalid settings key: " . __FUNCTION__);
        }
    }

    public function getDefaultResultsPage(): int
    {
        if (is_array($this->settings["common"]) && is_numeric($this->settings["common"]["defaultResultsPage"])) {
            return (intval($this->settings["common"]["defaultResultsPage"]));
        } else {
            throw new \RuntimeException("Invalid settings key: " . __FUNCTION__);
        }
    }
}
