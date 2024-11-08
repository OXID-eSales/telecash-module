<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\Core\Service;

use OxidEsales\Eshop\Core\Price as oxPrice;
use OxidSolutionCatalysts\TeleCash\Core\Service\Price;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Test cases for the Price service class
 *
 * Tests various price formatting scenarios including different currency formats
 * and decimal place configurations.
 */
class PriceTest extends TestCase
{
    /**
     * Test price formatting with various currency configurations
     *
     * @dataProvider priceFormattingProvider
     *
     * @param float $bruttoPrice The gross price to format
     * @param object $currency Currency configuration object
     * @param string $expectedResult Expected formatted string
     * @throws Exception
     */
    public function testFormattedBruttoPrice(
        float $bruttoPrice,
        object $currency,
        string $expectedResult
    ): void {
        // Arrange
        $oxPrice = $this->createMock(oxPrice::class);
        $oxPrice->method('getBruttoPrice')
            ->willReturn($bruttoPrice);

        $price = new Price($oxPrice, $currency);

        // Act
        $result = $price->getFormattedBruttoPrice();

        // Assert
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider for price formatting test cases
     *
     * Provides various test scenarios:
     * - German format (1.234,56)
     * - English format (1,234.56)
     * - Three decimal places
     * - No decimal places (with rounding)
     * - Missing currency settings (using defaults)
     *
     * @return array<string, array{bruttoPrice: float, currency: object, expectedResult: string}>
     */
    public function priceFormattingProvider(): array
    {
        return [
            'standard_de_format' => [
                'bruttoPrice' => 1234.56,
                'currency' => (object)[
                    'dec' => ',',
                    'thousand' => '.',
                    'decimal' => '2'
                ],
                'expectedResult' => '1.234,56'
            ],
            'standard_en_format' => [
                'bruttoPrice' => 1234.56,
                'currency' => (object)[
                    'dec' => '.',
                    'thousand' => ',',
                    'decimal' => '2'
                ],
                'expectedResult' => '1,234.56'
            ],
            'three_decimals' => [
                'bruttoPrice' => 1234.567,
                'currency' => (object)[
                    'dec' => ',',
                    'thousand' => '.',
                    'decimal' => '3'
                ],
                'expectedResult' => '1.234,567'
            ],
            'no_decimals' => [
                'bruttoPrice' => 1234.56,
                'currency' => (object)[
                    'dec' => ',',
                    'thousand' => '.',
                    'decimal' => '0'
                ],
                'expectedResult' => '1.235'  // Rounded automatically
            ],
            'missing_currency_settings' => [
                'bruttoPrice' => 1234.56,
                'currency' => (object)[],
                'expectedResult' => '1.234,56'  // Using default settings
            ],
        ];
    }
}