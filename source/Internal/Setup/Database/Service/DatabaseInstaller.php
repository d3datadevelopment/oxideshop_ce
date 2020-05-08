<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Internal\Setup\Database\Service;

use OxidEsales\EshopCommunity\Internal\Setup\ConfigFile\ConfigFileDaoInterface;
use OxidEsales\EshopCommunity\Internal\Setup\Database\Exception\DatabaseAlreadyExistsException;

class DatabaseInstaller implements DatabaseInstallerInterface
{
    /**
     * @var DatabaseCreatorInterface
     */
    private $creator;

    /**
     * @var DatabaseInitiatorInterface
     */
    private $initiator;

    /**
     * @var ConfigFileDaoInterface
     */
    private $configFileDao;

    public function __construct(
        DatabaseCreatorInterface $creator,
        DatabaseInitiatorInterface $initiator,
        ConfigFileDaoInterface $configFileDao
    ) {
        $this->creator = $creator;
        $this->initiator = $initiator;
        $this->configFileDao = $configFileDao;
    }


    public function install(string $host, int $port, string $username, string $password, string $name): void
    {
        try {
            $this->creator->createDatabase($host, $port, $username, $password, $name);
        } catch (DatabaseAlreadyExistsException $exception) {}

        $this->addCredentialsToConfigFile($host, $username, $password, $name);

        $this->initiator->initiateDatabase();
    }

    /**
     * @param string $host
     * @param string $username
     * @param string $password
     * @param string $name
     */
    private function addCredentialsToConfigFile(string $host, string $username, string $password, string $name): void
    {
        $this->configFileDao->replacePlaceholder('dbHost', $host);
        $this->configFileDao->replacePlaceholder('dbUser', $username);
        $this->configFileDao->replacePlaceholder('dbPwd', $password);
        $this->configFileDao->replacePlaceholder('dbName', $name);
    }
}
