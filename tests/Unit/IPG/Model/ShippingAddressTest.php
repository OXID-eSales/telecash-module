<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\IPG\Model;

use OxidSolutionCatalysts\TeleCash\IPG\Model\ShippingAddress;
use PHPUnit\Framework\TestCase;

/**
 * Test case for the ShippingAddress model
 * This class tests the functionality of mapping and storing shipping address data
 * according to the TeleCash payment processing requirements
 */
class ShippingAddressTest extends TestCase
{
    /**
     * Sample test data representing complete shipping address information
     * Contains all possible fields defined in the ShippingAddress model
     * plus an additional field to test field filtering
     * Note: Using realistic address data patterns
     *
     * @var array<string, string>
     */
    private array $testData = [
        'sname' => 'Jane Doe',                  // Recipient name
        'saddr1' => 'Shipping Street 123',      // Primary shipping address line
        'saddr2' => 'Floor 3',                  // Secondary address line
        'scity' => 'Shipping City',             // City name
        'sstate' => 'Shipping State',           // State/Province
        'scountry' => 'DE',                     // Country code (ISO)
        'szip' => '54321',                      // Postal code
        'unexpectedField' => 'should be ignored' // Additional field to test filtering
    ];

    /**
     * Tests the complete flow of parsing input data and retrieving it
     * Verifies that:
     * - All defined shipping address fields are processed correctly
     * - Field values are stored accurately
     * - Undefined fields are ignored
     * - The output matches the expected shipping address data structure
     */
    public function testParseFromArrayAndToArray(): void
    {
        // Create new instance with test data
        $shippingAddress = new ShippingAddress($this->testData);
        $result = $shippingAddress->toArray();

        // List of fields that should be handled by the model
        // These correspond to the fields defined in the ShippingAddress class
        $expectedFields = [
            'sname', 'saddr1', 'saddr2', 'scity',
            'sstate', 'scountry', 'szip'
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
     * Tests behavior when no shipping address data is provided
     * Verifies that the model handles empty input gracefully
     * and returns an empty array rather than null or throwing an exception
     */
    public function testWithEmptyData(): void
    {
        $shippingAddress = new ShippingAddress([]);
        $result = $shippingAddress->toArray();

        // Result should be empty array since no data was provided
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Tests behavior with partial shipping address data
     * Verifies that:
     * - Only provided fields are stored
     * - Missing fields are not included in the result
     * - The model handles incomplete shipping address data correctly
     */
    public function testWithPartialData(): void
    {
        // Setup test data with only a subset of shipping address fields
        $partialData = [
            'sname' => 'Jane Doe',
            'scity' => 'Shipping City'
        ];

        $shippingAddress = new ShippingAddress($partialData);
        $result = $shippingAddress->toArray();

        // Verify that only the provided fields are present
        $this->assertCount(2, $result);
        $this->assertEquals($partialData['sname'], $result['sname']);
        $this->assertEquals($partialData['scity'], $result['scity']);
    }
}
