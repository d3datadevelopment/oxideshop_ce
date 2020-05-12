<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace Integration\Internal\Setup;

use OxidEsales\Eshop\Core\Language;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionProviderInterface;
use OxidEsales\EshopCommunity\Internal\Framework\DIContainer\Service\ShopStateServiceInterface;
use OxidEsales\EshopCommunity\Internal\Setup\ConfigFile\ConfigFileIsAlreadySetupException;
use OxidEsales\EshopCommunity\Internal\Setup\ShopSetupCommand;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\BasicContextInterface;
use OxidEsales\EshopCommunity\Tests\Integration\Internal\ContainerTrait;
use OxidEsales\Facts\Config\ConfigFile;
use OxidEsales\TestingLibrary\Services\Library\DatabaseRestorer\DatabaseRestorer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class ShopSetupCommandTest extends TestCase
{
    use ContainerTrait;

    private $host;
    private $dbName;
    private $user;
    private $password;
    private $port;
    private $shopDirectory;
    private $compileDirectory;
    private $shopUrl;

    /**
     * @var DatabaseRestorer
     */
    private $databaseRestorer;

    protected function setup(): void
    {
        //$this->backupDatabase();
        //$this->dropDatabase();
        $this->closeDatabaseConnection();

        $this->setValuesFromOriginalConfig();
        $this->dropOriginalConfigFile();
        $this->prepareTestConfigFile();

        $shopStateService = $this->get(ShopStateServiceInterface::class);
        $this->assertFalse(
            $shopStateService->isLaunched()
        );
    }

    protected function tearDown(): void
    {
        $this->restoreConfigFile();
        //$this->restoreDatabase();
    }

    public function testSetup(): void
    {
        $commandTester = new CommandTester($this->get(ShopSetupCommand::class));
        $commandTester->execute([
            'host'              => $this->host,
            'dbname'            => 'testShopSetup',//$this->dbName,
            'port'              => $this->port,
            'user'              => $this->user,
            'password'          => $this->password,
            'shop-url'          => $this->shopUrl,
            'shop-directory'    => $this->shopDirectory,
            'compile-directory' => $this->compileDirectory,
            'language'          => 'en',
        ]);

        $this->assertTrue(
            $this->get(ShopStateServiceInterface::class)->isLaunched()
        );

        $this->assertConfigFileParameters();
        $this->assertLanguage();
    }

    public function testSetupThrowsExceptionOnTheSecondInstallation(): void
    {
//        $input = [
//            'host'              => $this->host,
//            'dbname'            => $this->dbName,
//            'port'              => $this->port,
//            'user'              => $this->user,
//            'password'          => $this->password,
//            'shop-url'          => $this->shopUrl,
//            'shop-directory'    => $this->shopDirectory,
//            'compile-directory' => $this->compileDirectory,
//            'language'          => 'en',
//        ];
//
//        $commandTester = new CommandTester($this->get(ShopSetupCommand::class));
//        $commandTester->execute($input);
//
//        $this->expectException(ConfigFileIsAlreadySetupException::class);
//        $commandTester->execute($input);
    }

    private function assertLanguage(): void
    {
        Registry::getConfig()->reinitialize();
        $this->assertSame(1, oxNew(Language::class)->getBaseLanguage());
    }

    private function backupDatabase(): void
    {
        $this->databaseRestorer = new DatabaseRestorer();
        $this->databaseRestorer->dumpDB(__CLASS__);
    }

    private function closeDatabaseConnection(): void
    {
        $this->get(ConnectionProviderInterface::class)->get()->close();
    }

    private function dropDatabase(): void
    {
        $connection = $this->get(ConnectionProviderInterface::class)->get();
        $connection->executeQuery('drop table oxconfig');
    }

    private function dropOriginalConfigFile(): void
    {
        $configFilePath = $this->get(BasicContextInterface::class)->getConfigFilePath();
        $fileSystem = $this->get('oxid_esales.symfony.file_system');

        $fileSystem->copy($configFilePath, $configFilePath . '.bak');
        $fileSystem->remove($configFilePath);
    }

    private function prepareTestConfigFile(): void
    {
        $configFilePath = $this->get(BasicContextInterface::class)->getConfigFilePath();
        $fileSystem = $this->get('oxid_esales.symfony.file_system');
        $fileSystem->copy($configFilePath . '.dist', $configFilePath );

        opcache_invalidate($configFilePath);
    }

    private function restoreConfigFile(): void
    {
        $configFilePath = $this->get(BasicContextInterface::class)->getConfigFilePath();
        $fileSystem = $this->get('oxid_esales.symfony.file_system');

        $fileSystem->remove($configFilePath);
        $fileSystem->copy( $configFilePath . '.bak', $configFilePath);
        $fileSystem->remove($configFilePath . '.bak');

        opcache_invalidate($configFilePath);
    }

    private function restoreDatabase(): void
    {
        $this->databaseRestorer->restoreDB(__CLASS__);
    }

    private function setValuesFromOriginalConfig(): void
    {
        $config = new ConfigFile();
        $this->host = $config->getVar('dbHost');
        $this->dbName = $config->getVar('dbName');
        $this->user = $config->getVar('dbUser');
        $this->password = $config->getVar('dbPwd');
        $this->port = $config->getVar('dbPort');
        $this->shopDirectory = $config->getVar('sShopDir');
        $this->compileDirectory = $config->getVar('sCompileDir');
        $this->shopUrl = $config->getVar('sShopURL');
    }

    private function assertConfigFileParameters(): void
    {
        $configFilePath = $this->get(BasicContextInterface::class)->getConfigFilePath();
        opcache_invalidate($configFilePath);

        $configFile = new ConfigFile();
        $this->assertSame($this->shopUrl, $configFile->getVar('sShopURL'));
        $this->assertSame($this->shopDirectory, $configFile->getVar('sShopDir'));
        $this->assertSame($this->compileDirectory, $configFile->getVar('sCompileDir'));
    }
}
