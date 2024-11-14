<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\IPG\Model;

use OxidSolutionCatalysts\TeleCash\IPG\Model\DirectDebitData;
use PHPUnit\Framework\TestCase;

/**
 * Test case for the DirectDebitData model
 * This class tests the functionality of mapping and storing SEPA direct debit data
 * according to the TeleCash direct debit processing requirements
 */
class DirectDebitDataTest extends TestCase
{
    /**
     * Sample test data representing complete direct debit information
     * Contains all possible fields defined in the DirectDebitData model
     * plus an additional field to test field filtering
     * Note: Using mock data that follows SEPA direct debit patterns
     *
     * @var array<string, string>
     */
    private array $testData = [
        'mandateReference' => 'MANDATE-2024-123456',    // Unique mandate reference number
        'mandateDate' => '2024-01-15',                  // Date when mandate was signed
        'mandateType' => 'RECURRING',                   // Type of mandate (ONE-OFF or RECURRING)
        'mandateUrl' => 'https://example.com/mandate',  // URL where mandate can be accessed
        'iban' => 'DE89370400440532013000',            // International Bank Account Number
        'bic' => 'DEUTDEBBXXX',                        // Bank Identifier Code
        'unexpectedField' => 'should be ignored'        // Additional field to test filtering
    ];

    /**
     * Tests the complete flow of parsing input data and retrieving it
     * Verifies that:
     * - All defined direct debit fields are processed correctly
     * - Field values are stored accurately
     * - Undefined fields are ignored
     * - The output matches the expected SEPA direct debit data structure
     */
    public function testParseFromArrayAndToArray(): void
    {
        // Create new instance with test data
        $directDebitData = new DirectDebitData($this->testData);
        $result = $directDebitData->toArray();

        // List of fields that should be handled by the model
        // These correspond to the fields defined in the DirectDebitData class
        $expectedFields = [
            'mandateReference', 'mandateDate', 'mandateType',
            'mandateUrl', 'iban', 'bic'
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
     * Tests behavior when no direct debit data is provided
     * Verifies that the model handles empty input gracefully
     * and returns an empty array rather than null or throwing an exception
     */
    public function testWithEmptyData(): void
    {
        $directDebitData = new DirectDebitData([]);
        $result = $directDebitData->toArray();

        // Result should be empty array since no data was provided
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Tests behavior with partial direct debit data
     * Verifies that:
     * - Only provided fields are stored
     * - Missing fields are not included in the result
     * - The model handles incomplete direct debit data correctly
     */
    public function testWithPartialData(): void
    {
        // Setup test data with only a subset of direct debit fields
        $partialData = [
            'iban' => 'DE89370400440532013000',
            'mandateReference' => 'MANDATE-2024-123456'
        ];

        $directDebitData = new DirectDebitData($partialData);
        $result = $directDebitData->toArray();

        // Verify that only the provided fields are present
        $this->assertCount(2, $result);
        $this->assertEquals($partialData['iban'], $result['iban']);
        $this->assertEquals($partialData['mandateReference'], $result['mandateReference']);
    }
}
