<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace Integration\Internal\Setup;

use OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionProviderInterface;
use OxidEsales\EshopCommunity\Internal\Framework\DIContainer\Service\ShopStateServiceInterface;
use OxidEsales\EshopCommunity\Internal\Setup\ShopSetupCommand;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\BasicContextInterface;
use OxidEsales\EshopCommunity\Tests\Integration\Internal\ContainerTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class ShopSetupCommandTest extends TestCase
{
    use ContainerTrait;

    private $testDatabaseName = 'oxid';

    protected function setup(): void
    {
        //$this->dropConfigTable();
        $this->prepareTestConfigFile();
        //$this->dropDatabase();
        $this->get(ConnectionProviderInterface::class)->get()->close();
    }

    protected function tearDown(): void
    {
        $this->restoreConfigFile();
    }

    public function testSetup(): void
    {
        $shopStateService = $this->get(ShopStateServiceInterface::class);
        $this->assertFalse(
            $shopStateService->isLaunched()
        );

        $context = $this->get(BasicContextInterface::class);

        $commandTester = new CommandTester($this->get(ShopSetupCommand::class));
        $commandTester->execute([
            'host'              => 'localhost',
            'dbname'            => 'oxid',
            'port'              => '3306',
            'user'              => 'oxid',
            'password'          => 'oxid',
            'shop-url'          => 'oxid.de',
            'shop-directory'    => $context->getShopRootPath(),
            'compile-directory' => $context->getShopRootPath() . '/tmp',
            'language'          => 'de',
        ]);

        $this->assertTrue(
            $shopStateService->isLaunched()
        );
    }

    private function dropConfigTable(): void
    {
        $connection = $this->get(ConnectionProviderInterface::class)->get();
        $connection->executeQuery('drop table oxconfig');
    }

    private function dropDatabase(): void
    {
        $connection = $this->get(ConnectionProviderInterface::class)->get();
        $connection->executeQuery('drop database oxid');
    }

    private function prepareTestConfigFile(): void
    {
        $configFilePath = $this->get(BasicContextInterface::class)->getConfigFilePath();
        $fileSystem = $this->get('oxid_esales.symfony.file_system');

        $fileSystem->copy($configFilePath, $configFilePath . '.bak');
        $fileSystem->remove($configFilePath);

        $fileSystem->copy($configFilePath . '.dist', $configFilePath );
    }

    private function restoreConfigFile(): void
    {
        $configFilePath = $this->get(BasicContextInterface::class)->getConfigFilePath();
        $fileSystem = $this->get('oxid_esales.symfony.file_system');

        $fileSystem->remove($configFilePath);
        $fileSystem->copy( $configFilePath . '.bak', $configFilePath);
        $fileSystem->remove($configFilePath . '.bak');
    }
}
