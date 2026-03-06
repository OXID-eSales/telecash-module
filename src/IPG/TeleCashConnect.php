<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\IPG;

use DateInvalidTimeZoneException;
use DateMalformedStringException;
use InvalidArgumentException;
use OxidSolutionCatalysts\TeleCash\Core\Service\Logger;
use OxidSolutionCatalysts\TeleCash\IPG\Model\BillingAddress;
use OxidSolutionCatalysts\TeleCash\IPG\Model\CreditCardData;
use OxidSolutionCatalysts\TeleCash\IPG\Model\CustomData;
use OxidSolutionCatalysts\TeleCash\IPG\Model\DirectDebitData;
use OxidSolutionCatalysts\TeleCash\IPG\Model\ShippingAddress;
use OxidSolutionCatalysts\TeleCash\IPG\Model\TransactionResult;
use OxidSolutionCatalysts\TeleCash\Traits\Json;
use OxidSolutionCatalysts\TeleCash\Traits\ServiceContainer;

/**
 * Base class for handling TeleCash Connect integration
 */
class TeleCashConnect
{
    use Json;
    use ServiceContainer;

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

    /**
     * Helper Class for Currency Handling
     */
    private TeleCashCurrency $teleCashCurrency;

    /**
     * Helper Class for DateTime Handling
     */
    private TeleCashDateTime $teleCashDateTime;

    private ?Logger $logger;

    public function __construct(
        string $storeName,
        string $secretKey,
        string $hashMethod = 'HMACSHA256'
    ) {
        $this->setContainer($this->getContainer());

        $this->hashMethod = $hashMethod;
        $this->secretKey = $secretKey;
        $this->storeName = $storeName;
        $this->teleCashCurrency = new TeleCashCurrency();
        $this->teleCashDateTime = new TeleCashDateTime();
        $this->logger = $this->getServiceFromContainer(Logger::class);
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
     * Returns the storename
     *
     * @return string
     */
    public function getStoreName(): string
    {
        return $this->storeName;
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
     * @param string $dateTime - e.g. '2024-10-14 18:06:39'
     * @param string $timeZone - e.g. 'Europe/Berlin'
     *
     * @return string
     */
    public function formatDateTime(string $dateTime = '', string $timeZone = ''): string
    {
        try {
            return $this->teleCashDateTime->formatDateTime($dateTime, $timeZone);
        } catch (DateInvalidTimeZoneException | DateMalformedStringException) {
            return '';
        }
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
        return $this->teleCashCurrency->getCurrencyCodeByShortname($shortName);
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
            $html .= sprintf(
                $tpl,
                htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
            );
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
     * @param array<string, string> $formFields
     * @return string
     */
    public function getHiddenFormFields(array $formFields): string
    {
        return $this->createHiddenFormFields($formFields);
    }

    /**
     * Validates the notification hash from the Telecash server. The hash is calculated from the response data and the
     * secret key, and must match the hash sent by the Telecash server.
     * Feed the $_POST array to this method, either from a success or a failed response.
     *
     * Basically the same as isValidResponse(), but for notifications.
     * Main difference is a different order of fields for the hash calculation.
     *
     * @param array<string, string> $data Response data to validate
     * @return bool True if notification is valid, false otherwise
     */
    public function isValidNotification(array $data = []): bool
    {
        if (empty($data) && !empty($this->responseData)) {
            $data = $this->responseData;
        }

        $toCheck = ['chargetotal', 'currency', 'txndatetime', 'storename', 'approval_code'];
        $hash = $data['notification_hash'] ?? '';

        return $this->calculateAndCompareHashes($data, $toCheck, $hash);
    }
    /**
     * Validates the response hash from the Telecash server. The hash is calculated from the response data and the
     * secret key, and must match the hash sent by the Telecash server.
     * Feed the $_POST array to this method, either from a success or a failed response.
     * Only valid for direct responses from Telecash, not for notifications (see "isValidNotification")!
     *
     * @param array<string, string> $data Response data to validate
     * @return bool True if response is valid, false otherwise
     */
    public function isValidResponse(array $data = []): bool
    {
        if (empty($data) && !empty($this->responseData)) {
            $data = $this->responseData;
        }

        $toCheck = ['approval_code', 'chargetotal', 'currency', 'txndatetime', 'storename'];
        $hash = $data['response_hash'] ?? '';

        return $this->calculateAndCompareHashes($data, $toCheck, $hash);
    }

    private const SENSITIVE_RESPONSE_FIELDS = [
        'cardnumber',
        'expmonth',
        'expyear',
        'cvm',
        'iban',
        'accountnumber',
        'bankcode',
        'approval_code',
        'response_hash',
        'notification_hash',
    ];

    /**
     * Sets the response data
     *
     * @param array<string, string> $data Response data to set
     */
    public function setResponseData(array $data): void
    {
        $this->responseData = $data;
        $this->logger?->log(
            'debug',
            'Debug: setResponseData: ' . $this->arrayToJson($this->maskSensitiveData($data))
        );
    }

    /**
     * Masks sensitive fields in data arrays before logging
     *
     * @param array<string, string> $data
     * @return array<string, string>
     */
    private function maskSensitiveData(array $data): array
    {
        $masked = $data;
        foreach ($masked as $key => $value) {
            if (in_array(strtolower($key), self::SENSITIVE_RESPONSE_FIELDS, true) && is_string($value)) {
                if (strlen($value) > 4) {
                    $masked[$key] = str_repeat('*', strlen($value) - 4) . substr($value, -4);
                } else {
                    $masked[$key] = '****';
                }
            }
        }
        return $masked;
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

    /**
     * Calculate a hash based on a list of fieldnames in `$toCheck` and response `$data` and compare it to given
     * `$compareHash`.
     *
     * @param array<string, string> $data The data where data is taken from
     * @param array<string> $toCheck The fields to check (must be set in `$data`)
     * @param string $compareHash The hash to compare to the calculated hash
     * @return bool True if the hashes match, false otherwise
     */
    private function calculateAndCompareHashes(array $data, array $toCheck, string $compareHash)
    {
        if (!isset($data['storename'])) {
            $data['storename'] = $this->storeName;
        }

        $checkData = [];
        foreach ($toCheck as $field) {
            if (array_key_exists($field, $data)) {
                $checkData[$field] = $data[$field];
            }
        }

        try {
            $hashMethod = $this->getHashMethodFromTeleCashData($data);
        } catch (InvalidArgumentException) {
            return false;
        }

        $hashAlgo = $this->getHashAlgorithm($hashMethod);
        $secretKey = $this->getSecretKey();
        $hash = $this->calculateHashFromData(
            $checkData,
            $hashAlgo,
            $secretKey
        );
        $result = hash_equals($hash, $compareHash);
        $this->logger?->log(
            'debug',
            'Debug: calculateAndCompareHashes: ' . ($result ? 'match' : 'mismatch')
        );

        return $result;
    }
}
