<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\IPG\Connect\Model;

use OxidSolutionCatalysts\TeleCash\IPG\Model\BillingAddress;
use PHPUnit\Framework\TestCase;

/**
 * Test case for the BillingAddress model
 * This class tests the functionality of mapping and storing billing address data
 * according to the TeleCash Connect API specifications
 */
class BillingAddressTest extends TestCase
{
    /**
     * Sample test data representing a complete billing address
     * Contains all possible fields defined in the BillingAddress model
     * plus an additional field to test field filtering
     *
     * @var array<string, string>
     */
    private array $testData = [
        'bcompany' => 'Test Company',
        'bname' => 'John Doe',              // Card holder name or SEPA account holder
        'baddr1' => 'Test Street 123',      // Primary address line
        'baddr2' => 'Apartment 4B',         // Secondary address line
        'bcity' => 'Test City',             // City name
        'bstate' => 'Test State',           // State/Province
        'bcountry' => 'DE',                 // Country code (ISO)
        'bzip' => '12345',                  // Postal code
        'phone' => '+49123456789',          // Contact phone
        'fax' => '+49123456780',            // Contact fax
        'email' => 'test@example.com',      // Contact email
        'someOtherField' => 'should be ignored' // Additional field to test filtering
    ];

    /**
     * Tests the complete flow of parsing input data and retrieving it
     * Verifies that:
     * - All defined fields are processed correctly
     * - Field values are stored accurately
     * - Undefined fields are ignored
     * - The output matches the expected structure
     */
    public function testParseFromArrayAndToArray(): void
    {
        // Create new instance with test data
        $billingAddress = new BillingAddress($this->testData);
        $result = $billingAddress->toArray();

        // List of fields that should be handled by the model
        // These correspond to the fields defined in the BillingAddress class
        $expectedFields = [
            'bcompany', 'bname', 'baddr1', 'baddr2', 'bcity',
            'bstate', 'bcountry', 'bzip', 'phone', 'fax', 'email'
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
        $this->assertArrayNotHasKey('someOtherField', $result);
    }

    /**
     * Tests behavior when no data is provided
     * Verifies that the model handles empty input gracefully
     * and returns an empty array rather than null or throwing an exception
     */
    public function testWithEmptyData(): void
    {
        $billingAddress = new BillingAddress([]);
        $result = $billingAddress->toArray();

        // Result should be empty array since no data was provided
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Tests behavior with partial data
     * Verifies that:
     * - Only provided fields are stored
     * - Missing fields are not included in the result
     * - The model handles incomplete data correctly
     */
    public function testWithPartialData(): void
    {
        // Setup test data with only a subset of fields
        $partialData = [
            'bname' => 'John Doe',
            'email' => 'test@example.com'
        ];

        $billingAddress = new BillingAddress($partialData);
        $result = $billingAddress->toArray();

        // Verify that only the provided fields are present
        $this->assertCount(2, $result);
        $this->assertEquals($partialData['bname'], $result['bname']);
        $this->assertEquals($partialData['email'], $result['email']);
    }
}
