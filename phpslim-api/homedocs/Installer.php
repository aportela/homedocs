<?php

namespace HomeDocs;

class Installer
{
    private \Psr\Log\LoggerInterface $logger;
    /**
     * @var array<string,mixed>
     */
    private array $settings = [];

    public function __construct(\Psr\Log\LoggerInterface $logger, \Psr\Container\ContainerInterface $container)
    {
        $this->logger = $logger;
        $this->settings = $container->get("settings");
    }

    public function __destruct() {}

    /**
     * @return array<string>
     */
    public function getMissingPHPExtensions(): array
    {
        return (array_diff($this->settings["phpRequiredExtensions"], get_loaded_extensions()));
    }

    public function checkRequiredPHPExtensions(): bool
    {
        $missingExtensions = $this->getMissingPHPExtensions();
        if (count($missingExtensions) > 0) {
            $this->logger->critical("Error: missing php extension/s: ", $missingExtensions);
            return (false);
        } else {
            return (true);
        }
    }
}
