<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\IPG\Connect;

use DateTime;
use InvalidArgumentException;
use OxidSolutionCatalysts\TeleCash\IPG\Model\BillingAddress;
use OxidSolutionCatalysts\TeleCash\IPG\Model\CreditCardData;
use OxidSolutionCatalysts\TeleCash\IPG\Model\CustomData;
use OxidSolutionCatalysts\TeleCash\IPG\Model\DirectDebitData;
use OxidSolutionCatalysts\TeleCash\IPG\Model\ShippingAddress;
use OxidSolutionCatalysts\TeleCash\IPG\Model\TransactionResult;

/**
 * Base class for handling TeleCash Connect integration
 */
class TeleCashConnect
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

    private string $hashMethod;
    private string $secretKey;
    private string $storeName;

    /**
     * List of post data fields and values
     *
     * @var array<string, string>
     */
    private array $postData = [];

    /**
     * List of response data fields and values
     *
     * @var array<string, string>
     */
    private array $responseData = [];

    /**
     * List of line items
     *
     * @var array<int, string>
     */
    private array $lineItems = [];

    public function __construct(string $storeName, string $secretKey, string $hashMethod = 'HMACSHA256')
    {
        $this->hashMethod = $hashMethod;
        $this->secretKey = $secretKey;
        $this->storeName = $storeName;
    }

    /**
     * Returns the shared secret
     *
     * @return string
     */
    private function getSecretKey(): string
    {
        return $this->secretKey;
    }

    /**
     * Returns the hash method
     *
     * @return string
     */
    public function getHashMethod(): string
    {
        return $this->hashMethod;
    }

    /**
     * Returns the hash Algorithm from a TeleCashResponse
     *
     * @param array<string, string> $data The response data
     * @throws InvalidArgumentException If hash method is not found in data
     */
    private function getHashMethodFromTeleCashData(array $data): string
    {
        if (!array_key_exists('hash_algorithm', $data)) {
            throw new InvalidArgumentException('Hash method not found');
        }
        return $data['hash_algorithm'];
    }

    /**
     * Returns the hash algorithm for a certain hash method. If the hash method is not known, an exception will
     * be thrown.
     *
     * @param string $hashMethod
     * @return string
     */
    private function getHashAlgorithm(string $hashMethod): string
    {
        $hashMethods = [
            'HMACSHA256' => 'sha256',
            'HMACSHA384' => 'sha384',
            'HMACSHA512' => 'sha512',
        ];
        if (!array_key_exists($hashMethod, $hashMethods)) {
            throw new InvalidArgumentException('Invalid hash method');
        }
        return $hashMethods[$hashMethod];
    }

    /**
     * Formats a DateTime object to a string, using the telecash-specific format.
     * Make sure that the timezone is set according to the form-value.
     *
     * @param DateTime $dateTime
     * @return string
     */
    public function formatDateTime(DateTime $dateTime): string
    {
        $format = 'Y:m:d-H:i:s';
        return $dateTime->format($format);
    }

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
                sprintf('Invalid currency code "%s"', $shortName)
            );
        }
        return self::CURRENCY_MAP[$shortName];
    }

    /**
     * Creates hidden form fields from an array of fields. The array must be associative, with the field name as key
     * and the field value as value. Add the hidden fields to the POST form to the Telecash server, proper info is
     * displayed in the Telecash dashboards.
     *
     * @param array<string, string> $fields Associative array of field names and values
     * @return string HTML string containing hidden input fields
     */
    public function createHiddenFormFields(array $fields): string
    {
        $html = '';
        foreach ($fields as $name => $value) {
            if (empty($value)) {
                continue;
            }
            $tpl = '<input type="hidden" name="%s" value="%s" />';
            $html .= sprintf($tpl, $name, $value);
        }

        return $html;
    }

    /**
     * Calculates the extended hash from an array of parameters. These parameters must be the ones posted to the
     * Telecash server, and the hash returned by this method must be added to the form with the name "hashExtended".
     * Note: if this key is found in the array, it will be removed before calculating the hash.
     *
     * @param array<string, string> $params Parameters to calculate hash from
     * @return string The calculated hash
     */
    public function calculateExtendedHashFromArray(array $params): string
    {
        if (array_key_exists('hashExtended', $params)) {
            unset($params['hashExtended']);
        }

        ksort($params, SORT_NATURAL);

        $hashAlgo = $this->getHashAlgorithm($this->getHashMethod());
        $secretKey = $this->getSecretKey();
        return $this->calculateHashFromData(
            $params,
            $hashAlgo,
            $secretKey
        );
    }

    /**
     * Merges multiple form field arrays into one. If a field is present in multiple arrays, the value from the last
     * array will be used, empty values will be ignored.
     * The resulting array must be used to calculate the extended hash (calculateExtendedHashFromArray).
     *
     * @param array<string, string> ...$formFieldArray Arrays to merge
     * @return array<string, string> Merged array
     */
    public function mergeFormFields(array ...$formFieldArray): array
    {
        $result = [];
        foreach ($formFieldArray as $formFields) {
            foreach ($formFields as $name => $value) {
                if (!empty($value)) {
                    $result[$name] = $value;
                }
            }
        }
        return $result;
    }

    /**
     * Resets the internal POST data array.
     */
    public function resetPostData(): void
    {
        $this->postData = [];
    }

    /**
     * Adds POST data to the internal array
     *
     * @param array<string, string> ...$postDataArrays Arrays of post data to add
     */
    public function addPostData(array ...$postDataArrays): void
    {
        foreach ($postDataArrays as $postData) {
            $this->postData = $this->mergeFormFields($this->postData, $postData);
        }
    }

    /**
     * Returns the internal POST data array
     *
     * @return array<string, string> Complete post data including calculated hash
     */
    public function getPostData(): array
    {
        $postData = $this->postData;
        foreach ($this->lineItems as $i => $lineItem) {
            $postData['item' . ($i + 1)] = $lineItem;
        }
        $postData['checkoutoption'] = 'combinedpage';
        $postData['storename'] = $this->storeName;
        $postData['hash_algorithm'] = $this->hashMethod;
        $postData['hashExtended'] = $this->calculateExtendedHashFromArray($postData);
        return $postData;
    }

    /**
     * Adds a line item to the internal line items array
     *
     * @param array<string, string> $lineItem Line item data
     */
    public function addLineItem(array $lineItem): void
    {
        $this->lineItems[] = implode(';', array_values($lineItem));
    }

    /**
     * Generates a list of hidden form fields from the internal POST data.
     * The hashExtended field will be calculated added to the list automatically.
     *
     * @return string
     */
    public function getHiddenFormFields(): string
    {
        return $this->createHiddenFormFields($this->getPostData());
    }

    /**
     * Validates the response hash from the Telecash server. The hash is calculated from the response data and the
     * secret key, and must match the hash sent by the Telecash server.
     * Feed the $_POST array to this method, either from a success or a failed response.
     *
     * @param array<string, string> $data Response data to validate
     * @return bool True if response is valid, false otherwise
     */
    public function isValidResponse(array $data = []): bool
    {
        if (empty($data) && !empty($this->responseData)) {
            $data = $this->responseData;
        }

        $checkData = [];
        $toCheck = ['approval_code', 'chargetotal', 'currency', 'txndatetime', 'storename'];

        foreach ($toCheck as $field) {
            if (array_key_exists($field, $data)) {
                $checkData[$field] = $data[$field];
            }
        }

        if (!isset($checkData['storename'])) {
            $checkData['storename'] = $this->storeName;
        }

        $hashAlgo = $this->getHashAlgorithm($this->getHashMethodFromTeleCashData($data));
        $secretKey = $this->getSecretKey();
        $hash = $this->calculateHashFromData(
            $checkData,
            $hashAlgo,
            $secretKey
        );

        return $hash === $data['response_hash'];
    }

    /**
     * Sets the response data
     *
     * @param array<string, string> $data Response data to set
     */
    public function setResponseData(array $data): void
    {
        $this->responseData = $data;
    }

    /**
     * Returns the billing address data
     *
     * @return array<string, string>
     */
    public function getBillingAddress(): array
    {
        return (new BillingAddress($this->responseData))->toArray();
    }

    /**
     * Returns the shipping address data
     *
     * @return array<string, string>
     */
    public function getShippingAddress(): array
    {
        return (new ShippingAddress($this->responseData))->toArray();
    }

    /**
     * Returns the transaction result data
     *
     * @return array<string, string>
     */
    public function getTransactionResult(): array
    {
        return (new TransactionResult($this->responseData))->toArray();
    }

    /**
     * Returns the credit card data
     *
     * @return array<string, string>
     */
    public function getCreditCardData(): array
    {
        return (new CreditCardData($this->responseData))->toArray();
    }

    /**
     * Returns the direct debit data
     *
     * @return array<string, string>
     */
    public function getDirectDebitData(): array
    {
        return (new DirectDebitData($this->responseData))->toArray();
    }

    /**
     * Returns the custom data
     *
     * @return array<string, string>
     */
    public function getCustomData(): array
    {
        return (new CustomData($this->responseData))->toArray();
    }

    /**
     * Calculates hash from data using specified algorithm and secret key
     *
     * @param array<string, string> $data Data to calculate hash from
     * @param string $hashAlgo Hash algorithm to use
     * @param string $secretKey Secret key for hash calculation
     * @return string Calculated hash
     */
    private function calculateHashFromData(array $data, string $hashAlgo, string $secretKey): string
    {
        $values = array_values($data);
        $stringToHash = implode('|', $values);
        return base64_encode(
            hash_hmac(
                $hashAlgo,
                $stringToHash,
                $secretKey,
                true
            )
        );
    }
}
