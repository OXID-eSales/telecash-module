<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\IPG\Model;

use OxidSolutionCatalysts\TeleCash\IPG\Model\CreditCardData;
use PHPUnit\Framework\TestCase;

/**
 * Test case for the CreditCardData model
 * This class tests the functionality of mapping and storing credit card data
 * according to the TeleCash payment processing requirements
 */
class CreditCardDataTest extends TestCase
{
    /**
     * Sample test data representing complete credit card information
     * Contains all possible fields defined in the CreditCardData model
     * plus an additional field to test field filtering
     * Note: Using mock data that follows typical credit card data patterns
     *
     * @var array<string, string>
     */
    private array $testData = [
        'ccbrand' => 'VISA',                    // Credit card brand/type
        'ccbin' => '411111',                    // Bank identification number (first 6 digits)
        'cccountry' => 'DE',                    // Card issuing country
        'expmonth' => '12',                     // Expiration month
        'expyear' => '2025',                    // Expiration year
        'cardnumber' => '4111111111111111',     // Full card number
        'cardLastFourDigits' => '1111',         // Last 4 digits of card
        'fundingCardNumberBin' => '411111',     // Funding card BIN
        'fundingCardNumberLast4' => '1111',     // Last 4 digits of funding card
        'unexpectedField' => 'should be ignored' // Additional field to test filtering
    ];

    /**
     * Tests the complete flow of parsing input data and retrieving it
     * Verifies that:
     * - All defined credit card fields are processed correctly
     * - Field values are stored accurately
     * - Undefined fields are ignored
     * - The output matches the expected credit card data structure
     */
    public function testParseFromArrayAndToArray(): void
    {
        // Create new instance with test data
        $creditCardData = new CreditCardData($this->testData);
        $result = $creditCardData->toArray();

        // List of fields that should be handled by the model
        // These correspond to the fields defined in the CreditCardData class
        $expectedFields = [
            'ccbrand', 'ccbin', 'cccountry', 'expmonth', 'expyear', 'cardnumber',
            'cardLastFourDigits', 'fundingCardNumberBin', 'fundingCardNumberLast4'
        ];

        // Verify each expected field
        foreach ($expectedFields as $field) {
            // Check if the field exists in the result
            $this->assertArrayHasKey($field, $result);
            // If we provided a value for this field, verify it was stored correctly
            if (array_key_exists($field, $this->testData)) {
                $this->assertEquals($this->testData[$field], $result[$field]);
            }
        }

        // Verify the integrity of the result
        $this->assertCount(count($expectedFields), $result);
        // Ensure additional fields were not included
        $this->assertArrayNotHasKey('unexpectedField', $result);
    }

    /**
     * Tests behavior when no credit card data is provided
     * Verifies that the model handles empty input gracefully
     * and returns an empty array rather than null or throwing an exception
     */
    public function testWithEmptyData(): void
    {
        $creditCardData = new CreditCardData([]);
        $result = $creditCardData->toArray();

        // Result should be empty array since no data was provided
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Tests behavior with partial credit card data
     * Verifies that:
     * - Only provided fields are stored
     * - Missing fields are not included in the result
     * - The model handles incomplete credit card data correctly
     */
    public function testWithPartialData(): void
    {
        // Setup test data with only a subset of credit card fields
        $partialData = [
            'ccbrand' => 'MASTERCARD',
            'cardLastFourDigits' => '9999'
        ];

        $creditCardData = new CreditCardData($partialData);
        $result = $creditCardData->toArray();

        // Verify that only the provided fields are present
        $this->assertCount(2, $result);
        $this->assertEquals($partialData['ccbrand'], $result['ccbrand']);
        $this->assertEquals($partialData['cardLastFourDigits'], $result['cardLastFourDigits']);
    }
}
