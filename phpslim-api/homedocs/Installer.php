<?php

declare(strict_types=1);

namespace HomeDocs;

class Installer
{
    /**
     * @var array<string,mixed>
     */
    private array $settings = [];

    public function __construct(private readonly \Psr\Log\LoggerInterface $logger, \Psr\Container\ContainerInterface $container)
    {
        $this->settings = $container->get("settings");
    }

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
        if ($missingExtensions !== []) {
            $this->logger->critical("Error: missing php extension/s: ", $missingExtensions);
            return (false);
        } else {
            return (true);
        }
    }
}
