<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\IPG;

use OxidSolutionCatalysts\TeleCash\IPG\TeleCashCurrency;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class TeleCashCurrencyTest extends TestCase
{
    private TeleCashCurrency $currency;

    protected function setUp(): void
    {
        $this->currency = new TeleCashCurrency();
    }

    /**
     * @test
     * @dataProvider validCurrencyMappingsProvider
     */
    public function getCurrencyCodeByShortnameWithValidShortnameReturnsCorrectCode(
        string $shortName,
        string $expectedCode
    ): void {
        $result = $this->currency->getCurrencyCodeByShortname($shortName);
        $this->assertEquals($expectedCode, $result);
    }

    /**
     * @test
     */
    public function getCurrencyCodeByShortnameWithInvalidShortnameThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid currency short name "XYZ"');

        $this->currency->getCurrencyCodeByShortname('XYZ');
    }

    /**
     * @test
     * @dataProvider validCurrencyMappingsProvider
     */
    public function getShortnameByCurrencyCodeWithValidCodeReturnsCorrectShortname(
        string $expectedShortName,
        string $currencyCode
    ): void {
        $result = $this->currency->getShortnameByCurrencyCode($currencyCode);
        $this->assertEquals($expectedShortName, $result);
    }

    /**
     * @test
     */
    public function getShortnameByCurrencyCodeWithInvalidCodeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid currency code "999"');

        $this->currency->getShortnameByCurrencyCode('999');
    }

    /**
     * @test
     */
    public function getCurrencyCodeByShortnameWithDefaultValueReturnsEuroCode(): void
    {
        $result = $this->currency->getCurrencyCodeByShortname();
        $this->assertEquals('978', $result);
    }

    /**
     * @test
     */
    public function getShortnameByCurrencyCodeWithDefaultValueReturnsEuroShortname(): void
    {
        $result = $this->currency->getShortnameByCurrencyCode();
        $this->assertEquals('EUR', $result);
    }

    /**
     * Provides test data for currency mappings
     * @return array<array{string, string}>
     */
    public static function validCurrencyMappingsProvider(): array
    {
        return [
            ['EUR', '978'],
            ['USD', '840'],
            ['GBP', '826'],
            ['CHF', '756'],
            ['JPY', '392'],
            ['AUD', '036'],
            ['CAD', '124'],
            ['CNY', '156'],
            ['SEK', '752'],
            ['NOK', '578']
        ];
    }
}
