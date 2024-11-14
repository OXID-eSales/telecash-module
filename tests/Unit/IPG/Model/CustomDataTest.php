<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\IPG\Model;

use OxidSolutionCatalysts\TeleCash\IPG\Model\CustomData;
use PHPUnit\Framework\TestCase;

/**
 * Test case for the CustomData model
 * This class tests the functionality of mapping and storing custom parameters
 * that are dynamically identified by the 'customParam_' prefix
 */
class CustomDataTest extends TestCase
{
    /**
     * Sample test data representing custom parameters
     * Contains various custom fields with the required prefix
     * plus fields that should be filtered out
     * Note: Using different types of custom parameters for comprehensive testing
     *
     * @var array<string, string>
     */
    private array $testData = [
        'customParam_orderId' => '12345',           // Custom order identifier
        'customParam_trackingId' => 'TRACK-789',    // Shipping tracking number
        'customParam_customerNote' => 'Handle with care', // Customer specific note
        'regularParam' => 'should be ignored',      // Non-custom parameter
        'customParam_' => 'empty suffix',           // Edge case: empty suffix
        'custom_wrongPrefix' => 'wrong prefix',     // Wrong prefix test
        'CUSTOMPARAM_case' => 'wrong case'         // Case sensitivity test
    ];

    /**
     * Tests the complete flow of parsing input data and retrieving it
     * Verifies that:
     * - Only fields with correct 'customParam_' prefix are processed
     * - Field values are stored accurately
     * - Non-matching fields are ignored
     * - The output contains only valid custom parameters
     */
    public function testParseFromArrayAndToArray(): void
    {
        // Create new instance with test data
        $customData = new CustomData($this->testData);
        $result = $customData->toArray();

        // Expected fields (only those with correct prefix)
        $expectedFields = [
            'customParam_orderId',
            'customParam_trackingId',
            'customParam_customerNote',
            'customParam_'
        ];

        // Verify each expected custom parameter
        foreach ($expectedFields as $field) {
            $this->assertArrayHasKey($field, $result);
            $this->assertEquals($this->testData[$field], $result[$field]);
        }

        // Verify the integrity of the result
        $this->assertCount(count($expectedFields), $result);

        // Ensure non-custom fields were not included
        $this->assertArrayNotHasKey('regularParam', $result);
        $this->assertArrayNotHasKey('custom_wrongPrefix', $result);
        $this->assertArrayNotHasKey('CUSTOMPARAM_case', $result);
    }

    /**
     * Tests behavior when no custom data is provided
     * Verifies that the model handles empty input gracefully
     * and returns an empty array rather than null or throwing an exception
     */
    public function testWithEmptyData(): void
    {
        $customData = new CustomData([]);
        $result = $customData->toArray();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Tests behavior with mixed valid and invalid custom parameters
     * Verifies that:
     * - Only valid custom parameters are stored
     * - Parameters with incorrect prefix are ignored
     * - The model correctly filters input data
     */
    public function testWithMixedData(): void
    {
        $mixedData = [
            'customParam_valid1' => 'First Valid Value',
            'customParam_valid2' => 'Second Valid Value',
            'invalid_param' => 'Invalid Value',
            'customParam_valid3' => 'Third Valid Value',
            'custom_invalid' => 'Another Invalid Value'
        ];

        $customData = new CustomData($mixedData);
        $result = $customData->toArray();

        // Verify only valid custom parameters are present
        $this->assertCount(3, $result);
        $this->assertArrayHasKey('customParam_valid1', $result);
        $this->assertArrayHasKey('customParam_valid2', $result);
        $this->assertArrayHasKey('customParam_valid3', $result);

        // Verify values are correctly stored
        $this->assertEquals('First Valid Value', $result['customParam_valid1']);
        $this->assertEquals('Second Valid Value', $result['customParam_valid2']);
        $this->assertEquals('Third Valid Value', $result['customParam_valid3']);

        // Verify invalid parameters are not present
        $this->assertArrayNotHasKey('invalid_param', $result);
        $this->assertArrayNotHasKey('custom_invalid', $result);
    }
}
