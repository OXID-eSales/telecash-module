<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\IPG;

use InvalidArgumentException;

/**
 * Class for handling TeleCash Currency
 */
class TeleCashCurrency
{
    /**
     * Currency mapping from ISO 4217 alpha codes to numeric codes
     *
     * @var array<string, string>
     */
    private const CURRENCY_MAP = [
        'AED' => '784', 'AFN' => '971', 'ALL' => '008', 'AMD' => '051', 'ANG' => '532',
        'AOA' => '973', 'ARS' => '032', 'AUD' => '036', 'AWG' => '533', 'AZN' => '944',
        'BAM' => '977', 'BBD' => '052', 'BDT' => '050', 'BGN' => '975', 'BHD' => '048',
        'BIF' => '108', 'BMD' => '060', 'BND' => '096', 'BOB' => '068', 'BOV' => '984',
        'BRL' => '986', 'BSD' => '044', 'BTN' => '064', 'BWP' => '072', 'BYN' => '933',
        'BZD' => '084', 'CAD' => '124', 'CDF' => '976', 'CHF' => '756', 'CLP' => '152',
        'CNY' => '156', 'COP' => '170', 'CRC' => '188', 'CUP' => '192', 'CVE' => '132',
        'CZK' => '203', 'DJF' => '262', 'DKK' => '208', 'DOP' => '214', 'DZD' => '012',
        'EGP' => '818', 'ERN' => '232', 'ETB' => '230', 'EUR' => '978', 'FJD' => '242',
        'FKP' => '238', 'GBP' => '826', 'GEL' => '981', 'GIP' => '292', 'GMD' => '270',
        'GNF' => '324', 'GTQ' => '320', 'GYD' => '328', 'HKD' => '344', 'HNL' => '340',
        'HRK' => '191', 'HTG' => '332', 'HUF' => '348', 'IDR' => '360', 'ILS' => '376',
        'INR' => '356', 'IQD' => '368', 'IRR' => '364', 'ISK' => '352', 'JMD' => '388',
        'JOD' => '400', 'JPY' => '392', 'KES' => '404', 'KGS' => '417', 'KHR' => '116',
        'KMF' => '174', 'KRW' => '410', 'KWD' => '414', 'KYD' => '136', 'KZT' => '398',
        'LAK' => '418', 'LBP' => '422', 'LKR' => '144', 'LRD' => '430', 'LSL' => '426',
        'LYD' => '434', 'MAD' => '504', 'MDL' => '498', 'MGA' => '969', 'MKD' => '807',
        'MMK' => '104', 'MNT' => '496', 'MOP' => '446', 'MRU' => '929', 'MUR' => '480',
        'MVR' => '462', 'MWK' => '454', 'MXN' => '484', 'MYR' => '458', 'MZN' => '943',
        'NAD' => '516', 'NGN' => '566', 'NIO' => '558', 'NOK' => '578', 'NPR' => '524',
        'NZD' => '554', 'OMR' => '512', 'PAB' => '590', 'PEN' => '604', 'PGK' => '598',
        'PHP' => '608', 'PKR' => '586', 'PLN' => '985', 'PYG' => '600', 'QAR' => '634',
        'RON' => '946', 'RSD' => '941', 'RUB' => '643', 'RWF' => '646', 'SAR' => '682',
        'SBD' => '090', 'SCR' => '690', 'SDG' => '938', 'SEK' => '752', 'SGD' => '702',
        'SHP' => '654', 'SLL' => '694', 'SOS' => '706', 'SRD' => '968', 'SSP' => '728',
        'STN' => '930', 'SVC' => '222', 'SYP' => '760', 'SZL' => '748', 'THB' => '764',
        'TJS' => '972', 'TMT' => '934', 'TND' => '788', 'TOP' => '776', 'TRY' => '949',
        'TTD' => '780', 'TWD' => '901', 'TZS' => '834', 'UAH' => '980', 'UGX' => '800',
        'USD' => '840', 'UYU' => '858', 'UZS' => '860', 'VES' => '928', 'VND' => '704',
        'VUV' => '548', 'WST' => '882', 'XAF' => '950', 'XCD' => '951', 'XOF' => '952',
        'XPF' => '953', 'YER' => '886', 'ZAR' => '710', 'ZMW' => '967', 'ZWL' => '932'
    ];

    /**
     * Returns the numeric currency code for the given ISO 4217 alpha code
     *
     * @param string $shortName ISO 4217 alpha code (e.g. 'EUR', 'USD')
     * @return string Numeric currency code
     * @throws InvalidArgumentException If currency code is not found
     */
    public function getCurrencyCodeByShortname(string $shortName = 'EUR'): string
    {
        if (!array_key_exists($shortName, self::CURRENCY_MAP)) {
            throw new InvalidArgumentException(
                sprintf('Invalid currency short name "%s"', $shortName)
            );
        }
        return self::CURRENCY_MAP[$shortName];
    }

    /**
     * Returns the numeric currency code for the given ISO 4217 alpha code
     *
     * @param string $currencyCode Numeric currency code
     * @return string ISO 4217 alpha code (e.g. 'EUR', 'USD')
     * @throws InvalidArgumentException If currency code is not found
     */
    public function getShortnameByCurrencyCode(string $currencyCode = '978'): string
    {
        $currencyMap = array_flip(self::CURRENCY_MAP);
        if (!array_key_exists($currencyCode, $currencyMap)) {
            throw new InvalidArgumentException(
                sprintf('Invalid currency code "%s"', $currencyCode)
            );
        }
        return $currencyMap[$currencyCode];
    }
}
