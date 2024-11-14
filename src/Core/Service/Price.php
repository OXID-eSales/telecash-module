<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Core\Service;

use OxidEsales\Eshop\Core\Price as oxPrice;

/**
 * Price formatting service class
 *
 * This class handles the formatting of prices according to currency-specific settings.
 * It works with the OXID price object and formats the price based on provided currency settings.
 */
class Price
{
    /**
     * OXID price object containing the actual price value
     */
    protected oxPrice $price;

    /**
     * Currency formatting settings object
     *
     * @property string $dec     Decimal separator (e.g., ',' for German or '.' for English format)
     * @property string $thousand Thousands separator (e.g., '.' for German or ',' for English format)
     * @property string $sign    Currency symbol
     * @property string $side    Currency symbol position
     * @property string $decimal Number of decimal places as string
     *
     * @var object
     */
    protected object $currency;

    /**
     * Constructor
     *
     * @param oxPrice $price    OXID price object containing the price value
     * @param object $currency  Currency formatting settings object
     */
    public function __construct(
        oxPrice $price,
        object $currency
    ) {
        $this->price = $price;
        $this->currency = $currency;
    }

    /**
     * Get the formatted gross price
     *
     * Retrieves the gross price from the OXID price object and formats it
     * according to the currency settings
     *
     * @return string Formatted price string (e.g., "1.234,56" for German format)
     */
    public function getFormattedBruttoPrice(): string
    {
        $price = $this->price->getBruttoPrice();
        return $this->getFormattedPrice($price);
    }

    /**
     * Format a price value according to currency settings
     *
     * Uses number_format() with the currency's decimal and thousand separator settings.
     * Falls back to German format (dec: ',', thousand: '.') if no settings provided.
     *
     * @param float $price The price value to format
     * @return string Formatted price string
     */
    private function getFormattedPrice(float $price): string
    {
        $decimalSeparator = $this->currency->dec ?? ',';
        $thousandsSeparator = $this->currency->thousand ?? '.';
        $decimals = isset($this->currency->decimal) ? (int) $this->currency->decimal : 2;

        return number_format(
            $price,
            $decimals,
            $decimalSeparator,
            $thousandsSeparator
        );
    }
}
