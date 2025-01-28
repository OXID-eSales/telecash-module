<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Integration\IPG;

use OxidEsales\Eshop\Core\Config;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashCurrency;
use PHPUnit\Framework\TestCase;
use OxidSolutionCatalysts\TeleCash\Core\Service\RegistryService;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ShopConfigurationDaoBridgeInterface;
use stdClass;

class TeleCashCurrencyIntegrationTest extends TestCase
{
    private TeleCashCurrency $currency;
    private $container;
    private $registryServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = ContainerFactory::getInstance()->getContainer();

        // Create mock for RegistryService
        $this->registryServiceMock = $this->createMock(RegistryService::class);

        // Create instance of TeleCashCurrency
        $this->currency = new TeleCashCurrency();

        // Set container in TeleCashCurrency using reflection
        $reflection = new \ReflectionClass($this->currency);
        $containerProperty = $reflection->getProperty('container');
        $containerProperty->setAccessible(true);
        $containerProperty->setValue($this->currency, $this->container);
    }

    /**
     * @test
     * @dataProvider currencyCodeProvider
     */
    public function getOxidCurrencyIdByCurrencyCodeWithValidCodeReturnsCorrectId(
        string $currencyCode,
        string $expectedCurrencyName,
        int $expectedId
    ): void {
        // Create a test currency array
        $testCurrency = new stdClass();
        $testCurrency->id = $expectedId;
        $testCurrency->name = $expectedCurrencyName;
        $testCurrency->rate = "1.00";
        $testCurrency->dec = "2";
        $testCurrency->sign = $expectedCurrencyName;

        // Create config mock
        $configMock = $this->createMock(Config::class);
        $configMock->method('getCurrencyArray')
            ->willReturn([$testCurrency]);
        $configMock->method('getShopCurrency')
            ->willReturn(0);

        // Configure RegistryService mock
        $this->registryServiceMock
            ->method('getConfig')
            ->willReturn($configMock);

        // Register RegistryService mock in the container
        $this->container->set(RegistryService::class, $this->registryServiceMock);

        // Test the method
        $result = $this->currency->getOxidCurrencyIdByCurrencyCode($currencyCode);

        $this->assertEquals($expectedId, $result);
    }

    /**
     * @test
     */
    public function getOxidCurrencyIdByCurrencyCodeWithInvalidCodeReturnsDefaultCurrencyId(): void
    {
        $defaultCurrencyId = 0;

        // Create config mock
        $configMock = $this->createMock(Config::class);
        $configMock->method('getCurrencyArray')
            ->willReturn([]);
        $configMock->method('getShopCurrency')
            ->willReturn($defaultCurrencyId);

        // Configure RegistryService mock
        $this->registryServiceMock
            ->method('getConfig')
            ->willReturn($configMock);

        // Register RegistryService mock in the container
        $this->container->set(RegistryService::class, $this->registryServiceMock);

        $result = $this->currency->getOxidCurrencyIdByCurrencyCode('999');

        $this->assertEquals($defaultCurrencyId, $result);
    }

    /**
     * @test
     */
    public function getOxidCurrencyIdByCurrencyCodeWithDefaultValueReturnsEuroCurrencyId(): void
    {
        // Setup EUR as currency
        $eurCurrency = new stdClass();
        $eurCurrency->id = 0;
        $eurCurrency->name = 'EUR';
        $eurCurrency->rate = "1.00";
        $eurCurrency->dec = "2";
        $eurCurrency->sign = 'EUR';

        // Create config mock
        $configMock = $this->createMock(Config::class);
        $configMock->method('getCurrencyArray')
            ->willReturn([$eurCurrency]);
        $configMock->method('getShopCurrency')
            ->willReturn(0);

        // Configure RegistryService mock
        $this->registryServiceMock
            ->method('getConfig')
            ->willReturn($configMock);

        // Register RegistryService mock in the container
        $this->container->set(RegistryService::class, $this->registryServiceMock);

        $result = $this->currency->getOxidCurrencyIdByCurrencyCode();

        $this->assertEquals(0, $result);
    }

    /**
     * Provides test data for currency codes and their expected OXID currency IDs
     * @return array<array{string, string, int}>
     */
    public static function currencyCodeProvider(): array
    {
        return [
            ['978', 'EUR', 0],  // Euro
            ['840', 'USD', 1],  // US Dollar
            ['826', 'GBP', 2],  // British Pound
            ['756', 'CHF', 3],  // Swiss Franc
        ];
    }
}
