<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Internal\Setup;

use OxidEsales\EshopCommunity\Internal\Setup\ConfigFile\ConfigFileDaoInterface;
use OxidEsales\EshopCommunity\Internal\Setup\Database\Service\DatabaseCreatorInterface;
use OxidEsales\EshopCommunity\Internal\Setup\Database\Service\DatabaseInitiatorInterface;
use OxidEsales\EshopCommunity\Internal\Setup\Database\Service\DatabaseInstallerInterface;
use OxidEsales\EshopCommunity\Internal\Setup\Directory\Service\DirectoryServiceInterface;
use OxidEsales\EshopCommunity\Internal\Setup\Htaccess\HtaccessUpdateServiceInterface;
use OxidEsales\EshopCommunity\Internal\Setup\Language\LanguageInstallerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShopSetupCommand extends Command
{
    /**
     * @var DatabaseInstallerInterface
     */
    private $databaseInstaller;

    /**
     * @var ConfigFileDaoInterface
     */
    private $configFileDao;

    /**
     * @var DirectoryServiceInterface
     */
    private $directoriesValidator;

    /**
     * @var LanguageInstallerInterface
     */
    private $languageInstaller;

    public function __construct(
        DatabaseInstallerInterface $databaseInstaller,
        ConfigFileDaoInterface $configFileDao,
        DirectoryServiceInterface $directoriesValidator,
        LanguageInstallerInterface $languageInstaller
    ) {
        parent::__construct();

        $this->databaseInstaller = $databaseInstaller;
        $this->configFileDao = $configFileDao;
        $this->directoriesValidator = $directoriesValidator;
        $this->languageInstaller = $languageInstaller;
    }

    protected function configure()
    {
        $this
            ->addArgument('host', InputArgument::REQUIRED)
            ->addArgument('port', InputArgument::REQUIRED)
            ->addArgument('dbname', InputArgument::REQUIRED)
            ->addArgument('user', InputArgument::REQUIRED)
            ->addArgument('password', InputArgument::REQUIRED)
            ->addArgument('shop-url', InputArgument::REQUIRED)
            ->addArgument('shop-directory', InputArgument::REQUIRED)
            ->addArgument('compile-directory', InputArgument::REQUIRED)
            ->addArgument('language', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->installDatabase($input);

        return 0;
    }

    /**
     * @param InputInterface $input
     */
    protected function installDatabase(InputInterface $input): void
    {
        $this->databaseInstaller->install(
            $input->getArgument('host'),
            (int) $input->getArgument('port'),
            $input->getArgument('user'),
            $input->getArgument('password'),
            $input->getArgument('dbname')
        );
    }
}
