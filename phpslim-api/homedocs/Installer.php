<?php

declare(strict_types=1);

namespace HomeDocs;

class Installer
{
    private \HomeDocs\Settings $settings;

    public function __construct(private readonly \Psr\Log\LoggerInterface $logger)
    {
        $this->settings = new \HomeDocs\Settings();
    }

    /**
     * @return array<string>
     */
    public function getMissingPHPExtensions(): array
    {
        return (array_diff(\HomeDocs\Settings::PHP_REQUIRED_EXTENSIONS, get_loaded_extensions()));
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
