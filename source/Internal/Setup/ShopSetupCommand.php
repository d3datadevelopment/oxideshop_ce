<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Internal\Setup;

use OxidEsales\EshopCommunity\Internal\Setup\ConfigFile\ConfigFileDaoInterface;
use OxidEsales\EshopCommunity\Internal\Setup\Database\Service\DatabaseInstallerInterface;
use OxidEsales\EshopCommunity\Internal\Setup\Directory\Service\DirectoryServiceInterface;
use OxidEsales\EshopCommunity\Internal\Setup\Htaccess\HtaccessUpdateServiceInterface;
use OxidEsales\EshopCommunity\Internal\Setup\Language\DefaultLanguage;
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

    /**
     * @var HtaccessUpdateServiceInterface
     */
    private $htaccessUpdateService;

    public function __construct(
        DatabaseInstallerInterface $databaseInstaller,
        ConfigFileDaoInterface $configFileDao,
        DirectoryServiceInterface $directoriesValidator,
        LanguageInstallerInterface $languageInstaller,
        HtaccessUpdateServiceInterface $htaccessUpdateService
    ) {
        parent::__construct();

        $this->databaseInstaller = $databaseInstaller;
        $this->configFileDao = $configFileDao;
        $this->directoriesValidator = $directoriesValidator;
        $this->languageInstaller = $languageInstaller;
        $this->htaccessUpdateService = $htaccessUpdateService;
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
            ->addArgument('language', InputArgument::OPTIONAL, '', 'en');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $language = new DefaultLanguage($input->getArgument('language'));

        $this->installDatabase($input);
        $this->languageInstaller->install($language);
        $this->updateConfigFile($input);

        return 0;
    }

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

    private function updateConfigFile(InputInterface $input): void
    {
        $this->configFileDao->replacePlaceholder('sShopURL', $input->getArgument('shop-url'));
        $this->configFileDao->replacePlaceholder('sShopDir', $input->getArgument('shop-directory'));
        $this->configFileDao->replacePlaceholder('sCompileDir', $input->getArgument('compile-directory'));
    }
}
