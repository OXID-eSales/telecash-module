<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\IPG\Model;

use OxidSolutionCatalysts\TeleCash\IPG\Model\TransactionResult;
use PHPUnit\Framework\TestCase;

/**
 * Test case for the TransactionResult model
 * This class tests the functionality of mapping and storing transaction result data
 * according to the TeleCash payment processing responses
 */
class TransactionResultTest extends TestCase
{
    /**
     * Sample test data representing a complete transaction result
     * Contains all possible fields defined in the TransactionResult model
     * plus an additional field to test field filtering
     * Note: Using mock data that follows typical transaction response patterns
     *
     * @var array<string, string>
     */
    private array $testData = [
        'txntype' => 'sale',                    // Transaction type
        'txndatetime' => '2024-01-15 14:30:00', // Transaction timestamp
        'txndate_processed' => '2024-01-15',    // Processing date
        'timezone' => 'Europe/Berlin',          // Transaction timezone
        'oid' => 'ORDER-123456',               // Order identifier
        'tdate' => '1234567890',               // Transaction date reference
        'approval_code' => 'ABC123',           // Authorization approval code
        'response_hash' => 'hash_value_here',  // Response verification hash
        'response_code_3dsecure' => '1',       // 3D Secure response code
        'hash_algorithm' => 'HMACSHA256',      // Hash algorithm used
        'processor_response_code' => '00',     // Processor's response code
        'endpointTransactionId' => 'EP12345',  // Endpoint specific transaction ID
        'terminal_id' => 'TERM123',           // Terminal identifier
        'transactionNotificationURL' => 'https://example.com/notify', // Notification endpoint
        'currency' => 'EUR',                  // Transaction currency
        'chargetotal' => '99.99',            // Total transaction amount
        'installments_interest' => '0.00',    // Interest for installments
        'customerid' => 'CUST123',           // Customer identifier
        'refnumber' => 'REF123456',          // Reference number
        'paymentMethod' => 'CC',             // Payment method used
        'ipgTransactionId' => 'IPG123456',   // IPG transaction identifier
        'status' => 'APPROVED',              // Transaction status
        'fail_rc' => '',                     // Failure reason code
        'fail_reason' => '',                 // Failure reason description
        'merchantTransactionId' => 'M123456', // Merchant's transaction ID
        'storename' => 'TestStore',          // Store identifier
        'schemeTransactionId' => 'SCH123456', // Scheme transaction ID
        'unexpectedField' => 'should be ignored' // Additional field to test filtering
    ];

    /**
     * Tests the complete flow of parsing input data and retrieving it
     * Verifies that:
     * - All defined transaction result fields are processed correctly
     * - Field values are stored accurately
     * - Undefined fields are ignored
     * - The output matches the expected transaction result structure
     */
    public function testParseFromArrayAndToArray(): void
    {
        // Create new instance with test data
        $transactionResult = new TransactionResult($this->testData);
        $result = $transactionResult->toArray();

        // List of fields that should be handled by the model
        // These correspond to the fields defined in the TransactionResult class
        $expectedFields = [
            'txntype', 'txndatetime', 'txndate_processed', 'timezone', 'oid', 'tdate',
            'approval_code', 'response_hash', 'response_code_3dsecure', 'hash_algorithm',
            'processor_response_code', 'endpointTransactionId', 'terminal_id', 'transactionNotificationURL',
            'currency', 'chargetotal', 'installments_interest', 'customerid', 'refnumber',
            'paymentMethod', 'ipgTransactionId', 'status', 'fail_rc', 'fail_reason',
            'merchantTransactionId', 'storename', 'schemeTransactionId'
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
     * Tests behavior when no transaction result data is provided
     * Verifies that the model handles empty input gracefully
     * and returns an empty array rather than null or throwing an exception
     */
    public function testWithEmptyData(): void
    {
        $transactionResult = new TransactionResult([]);
        $result = $transactionResult->toArray();

        // Result should be empty array since no data was provided
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Tests behavior with partial transaction result data
     * Verifies that:
     * - Only provided fields are stored
     * - Missing fields are not included in the result
     * - The model handles incomplete transaction data correctly
     */
    public function testWithPartialData(): void
    {
        // Setup test data with only essential transaction fields
        $partialData = [
            'txntype' => 'sale',
            'status' => 'APPROVED',
            'chargetotal' => '99.99'
        ];

        $transactionResult = new TransactionResult($partialData);
        $result = $transactionResult->toArray();

        // Verify that only the provided fields are present
        $this->assertCount(3, $result);
        $this->assertEquals($partialData['txntype'], $result['txntype']);
        $this->assertEquals($partialData['status'], $result['status']);
        $this->assertEquals($partialData['chargetotal'], $result['chargetotal']);
    }
}
