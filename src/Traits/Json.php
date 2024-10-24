<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Traits;

use JsonException;
use OxidSolutionCatalysts\TeleCash\Core\Service\RegistryService;

/**
 * Trait Json
 *
 * A convenience trait for handling JSON operations in controllers.
 * Provides functionality for:
 * - Reading JSON POST data
 * - Converting between JSON strings and arrays
 * - Sending JSON responses
 *
 * Requirements:
 * - The using class must have access to the RegistryService via $registryService property
 *
 * @property RegistryService $registryService Service for accessing shop utilities
 */
trait Json
{
    /**
     * Retrieves raw POST data from the PHP input stream
     *
     * This method is useful for accessing POST data that isn't in
     * typical application/x-www-form-urlencoded format.
     *
     * @return string Raw POST data or empty string if no data available
     */
    protected function getJsonPostData(): string
    {
        $result = file_get_contents('php://input');
        return $result ?: '';
    }

    /**
     * Converts a JSON string to an array
     *
     * This method attempts to decode the JSON string and ensures the result
     * is an array. If the decoding fails or produces a non-array result,
     * an empty array is returned.
     *
     * @param string $json The JSON string to decode
     * @return array<string|int, mixed> Decoded array or empty array on failure
     *                                  Keys can be strings or integers,
     *                                  values can be of any type
     */
    protected function jsonToArray(string $json): array
    {
        try {
            $result = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($result)) {
                throw new JsonException('Decoded JSON is not an array');
            }
        } catch (JsonException) {
            $result = [];
        }
        return $result;
    }

    /**
     * Converts an array to a JSON string
     *
     * This method encodes an array to JSON format with the following options:
     * - Pretty printing enabled for readability
     * - Numeric strings are converted to numbers
     * - Throws exceptions on encoding errors
     *
     * @param array<string|int, mixed> $data Array to encode. Keys can be strings
     *                                       or integers, values can be of any type
     * @return string JSON encoded string or empty string on failure
     */
    protected function arrayToJson(array $data): string
    {
        try {
            $result = json_encode(
                $data,
                JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK
            );
        } catch (JsonException) {
            $result = '';
        }
        return $result;
    }

    /**
     * Outputs data as a JSON response and ends script execution
     *
     * This method:
     * 1. Sets the appropriate Content-Type header for JSON
     * 2. Outputs the JSON string
     * 3. Terminates script execution
     *
     * Important: This method will terminate script execution after sending
     * the response. Any code after calling this method will not be executed.
     *
     * @param string $json The JSON string to output
     * @return void Does not return as script execution is terminated
     */
    protected function outputJson(string $json): void
    {
        $utils = $this->registryService->getUtils();
        $utils->setHeader('Content-Type: application/json');
        $utils->showMessageAndExit($json);
    }
}
