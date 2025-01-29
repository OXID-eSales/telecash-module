<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Integration\IPG;

use OxidEsales\Eshop\Core\Config;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashCurrency;
use PHPUnit\Framework\TestCase;
use OxidSolutionCatalysts\TeleCash\Core\Service\RegistryService;
use stdClass;
use Psr\Container\ContainerInterface;

class TeleCashCurrencyTest extends TestCase
{
    private TeleCashCurrency $currency;
    private $registryServiceMock;
    private $containerMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Create mock for RegistryService
        $this->registryServiceMock = $this->createMock(RegistryService::class);

        // Create a more specific container mock
        $this->containerMock = $this->createMock(ContainerInterface::class);
        $this->containerMock->method('has')
            ->with(RegistryService::class)
            ->willReturn(true);
        $this->containerMock->method('get')
            ->with(RegistryService::class)
            ->willReturn($this->registryServiceMock);

        // Create instance of TeleCashCurrency
        $this->currency = new TeleCashCurrency();

        // Inject the container mock
        $reflection = new \ReflectionClass($this->currency);
        $containerProperty = $reflection->getProperty('container');
        $containerProperty->setAccessible(true);
        $containerProperty->setValue($this->currency, $this->containerMock);
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
        // Create all test currencies
        $currencies = [];

        // EUR
        $eurCurrency = new stdClass();
        $eurCurrency->id = 0;
        $eurCurrency->name = 'EUR';
        $eurCurrency->rate = "1.00";
        $eurCurrency->dec = "2";
        $eurCurrency->sign = 'EUR';
        $currencies[] = $eurCurrency;

        // USD
        $usdCurrency = new stdClass();
        $usdCurrency->id = 1;
        $usdCurrency->name = 'USD';
        $usdCurrency->rate = "1.08";
        $usdCurrency->dec = "2";
        $usdCurrency->sign = 'USD';
        $currencies[] = $usdCurrency;

        // GBP
        $gbpCurrency = new stdClass();
        $gbpCurrency->id = 2;
        $gbpCurrency->name = 'GBP';
        $gbpCurrency->rate = "0.86";
        $gbpCurrency->dec = "2";
        $gbpCurrency->sign = 'GBP';
        $currencies[] = $gbpCurrency;

        // CHF
        $chfCurrency = new stdClass();
        $chfCurrency->id = 3;
        $chfCurrency->name = 'CHF';
        $chfCurrency->rate = "0.96";
        $chfCurrency->dec = "2";
        $chfCurrency->sign = 'CHF';
        $currencies[] = $chfCurrency;

        // Create config mock
        $configMock = $this->createMock(Config::class);
        $configMock->method('getCurrencyArray')
            ->willReturn($currencies);
        $configMock->method('getShopCurrency')
            ->willReturn(0);

        // Configure RegistryService mock
        $this->registryServiceMock
            ->method('getConfig')
            ->willReturn($configMock);

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
