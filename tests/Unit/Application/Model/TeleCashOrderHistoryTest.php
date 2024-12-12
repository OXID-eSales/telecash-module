<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\Application\Model;

use DateTime;
use OxidSolutionCatalysts\TeleCash\Tests\Unit\Application\Model\TestClasses\TeleCashOrderHistoryTestClass;
use PHPUnit\Framework\TestCase;

/**
 * Unit Test Suite for TeleCashOrderHistory
 *
 * Tests the business logic of the TeleCashOrderHistory class without database dependencies.
 * Focuses on transaction data handling, value conversions and data validation.
 */
class TeleCashOrderHistoryTest extends TestCase
{
    private array $sampleTransactionData;
    private TeleCashOrderHistoryTestClass $orderHistory;

    /**
     * Set up test environment
     *
     * Initializes a test instance of TeleCashOrder and prepares sample transaction
     * data that represents a typical payment transaction.
     */
    protected function setUp(): void
    {
        // Initialize test class without database connection
        $this->orderHistory = new TeleCashOrderHistoryTestClass('testOrderId');

        // Prepare comprehensive sample transaction data
        $this->sampleTransactionData = [
            'txntype' => 'sale',
            'txndatetime' => '2024-01-15 10:30:00',
            'oid' => 'test123',
            'endpointTransactionId' => 'endpoint123',
            'terminal_id' => 'term123',
            'ipgTransactionId' => 'ipg123',
            'currency' => '978',  // EUR in numeric format
            'chargetotal' => '99.99',
            'status' => 'APPROVED',
            'processor_response_code' => '00',  // Note: Leading zero must be preserved
            'paymentMethod' => 'V'  // VISA code
        ];

        $this->orderHistory->setTransactionResult($this->sampleTransactionData);
    }

    /**
     * Test transaction type retrieval and validation
     *
     * Verifies that:
     * - Valid transaction types are correctly returned
     * - Invalid types are handled properly (empty string)
     */
    public function testGetTxnType(): void
    {
        $this->assertEquals('sale', $this->orderHistory->getTxnType());

        // Test invalid txntype
        $invalidData = $this->sampleTransactionData;
        $invalidData['txntype'] = 'invalid';
        $this->orderHistory->setTransactionResult($invalidData);
        $this->assertEquals('', $this->orderHistory->getTxnType());
    }

    /**
     * Test transaction datetime retrieval and validation
     *
     * Ensures the datetime string is properly parsed and
     * handles invalid formats correctly
     */
    public function testGetTxnDateTime(): void
    {
        // Test valid date
        $validData = $this->sampleTransactionData;
        $validData['txndatetime'] = '2024:01:15-10:30:00';
        $this->orderHistory->setTransactionResult($validData);

        $result = $this->orderHistory->getTxnDateTime();
        $this->assertInstanceOf(DateTime::class, $result);
        $this->assertEquals('2024-01-15 10:30:00', $result->format('Y-m-d H:i:s'));

        // Test invalid date format
        $invalidData = $this->sampleTransactionData;
        $invalidData['txndatetime'] = 'invalid-date-format';
        $this->orderHistory->setTransactionResult($invalidData);
        $this->assertNull($this->orderHistory->getTxnDateTime());

        // Test empty date
        $emptyData = $this->sampleTransactionData;
        $emptyData['txndatetime'] = '';
        $this->orderHistory->setTransactionResult($emptyData);
        $this->assertNull($this->orderHistory->getTxnDateTime());
    }

    /**
     * Test order ID retrieval
     *
     * Verifies the original order ID is correctly returned
     */
    public function testGetOid(): void
    {
        $this->assertEquals('test123', $this->orderHistory->getOid());
    }

    /**
     * Test endpoint transaction ID retrieval
     *
     * Ensures the unique transaction identifier is properly returned
     */
    public function testGetEndpointTransactionId(): void
    {
        $this->assertEquals('endpoint123', $this->orderHistory->getEndpointTransactionId());
    }

    /**
     * Test terminal ID retrieval
     *
     * Verifies the correct terminal identification is returned
     */
    public function testGetTerminalId(): void
    {
        $this->assertEquals('term123', $this->orderHistory->getTerminalId());
    }

    /**
     * Test IPG transaction ID retrieval
     *
     * Ensures the correct IPG transaction identifier is returned
     */
    public function testGetIpgTransactionId(): void
    {
        $this->assertEquals('ipg123', $this->orderHistory->getIpgTransactionId());
    }

    /**
     * Test currency code conversion and validation
     *
     * Verifies that:
     * - Numeric currency codes are correctly converted to ISO codes
     * - Invalid codes are handled properly (empty string)
     */
    public function testGetCurrency(): void
    {
        $this->assertEquals('978', $this->orderHistory->getCurrency());

        // Test invalid currency
        $invalidData = $this->sampleTransactionData;
        $invalidData['currency'] = 'invalid';
        $this->orderHistory->setTransactionResult($invalidData);
        $this->assertEquals('', $this->orderHistory->getCurrency());
    }

    /**
     * Test currency (in oxid-style) code conversion and validation
     *
     * Verifies that:
     * - Numeric currency codes are correctly converted to ISO codes
     * - Invalid codes are handled properly (empty string)
     */
    public function testGetOxidCurrency(): void
    {
        $this->assertEquals('EUR', $this->orderHistory->getOxidCurrency());

        // Test invalid currency
        $invalidData = $this->sampleTransactionData;
        $invalidData['currency'] = 'invalid';
        $this->orderHistory->setTransactionResult($invalidData);
        $this->assertEquals('', $this->orderHistory->getOxidCurrency());
    }

    /**
     * Test charge total handling
     *
     * Verifies that:
     * - Valid amounts are correctly formatted with two decimal places
     * - German decimal separator is properly handled
     * - Invalid amounts return "0.00"
     */
    public function testGetChargeTotal(): void
    {
        // Test valid amount
        $this->assertEquals('99.99', $this->orderHistory->getChargeTotal());

        // Test German decimal separator
        $germanData = $this->sampleTransactionData;
        $germanData['chargetotal'] = '99,99';
        $this->orderHistory->setTransactionResult($germanData);
        $this->assertEquals('99.99', $this->orderHistory->getChargeTotal());

        // Test invalid charge total
        $invalidData = $this->sampleTransactionData;
        $invalidData['chargetotal'] = 'invalid';
        $this->orderHistory->setTransactionResult($invalidData);
        $this->assertEquals('0.00', $this->orderHistory->getChargeTotal());

        // Test with integer value
        $integerData = $this->sampleTransactionData;
        $integerData['chargetotal'] = '100';
        $this->orderHistory->setTransactionResult($integerData);
        $this->assertEquals('100.00', $this->orderHistory->getChargeTotal());
    }

    /**
     * Test charge total (in oxid style as float) handling
     *
     * Verifies that:
     * - Valid amounts are correctly converted to float
     * - Invalid amounts are handled properly (0.0)
     */
    public function testGetOxidChargeTotal(): void
    {
        $this->assertEquals(99.99, $this->orderHistory->getOxidChargeTotal());

        // Test invalid charge total
        $invalidData = $this->sampleTransactionData;
        $invalidData['chargetotal'] = 'invalid';
        $this->orderHistory->setTransactionResult($invalidData);
        $this->assertEquals(0.0, $this->orderHistory->getOxidChargeTotal());
    }

    /**
     * Test status retrieval
     *
     * Ensures the transaction status is correctly returned
     */
    public function testGetStatus(): void
    {
        $this->assertEquals('APPROVED', $this->orderHistory->getStatus());
    }

    /**
     * Test processor response code handling
     *
     * Verifies that the response code is returned exactly as received,
     * preserving leading zeros
     */
    public function testGetProcessorResponseCode(): void
    {
        $this->assertEquals('00', $this->orderHistory->getProcessorResponseCode());
    }

    /**
     * Test payment method mapping and validation
     *
     * Tests various payment method scenarios:
     * - Credit card codes (A, M, V)
     * - Direct debit (debitDE)
     * - PayPal
     * - Empty values
     * - Invalid codes
     *
     * Verifies correct mapping between external codes and internal identifiers
     */
    public function testGetPaymentMethodVariants(): void
    {
        $testCases = [
            'A' => 'cc_american',        // American Express
            'M' => 'cc_mastercard',      // Mastercard
            'V' => 'cc_visa',            // Visa
            'debitDE' => 'sepa',         // SEPA Direct Debit
            'paypal' => 'paypal',        // PayPal
            '' => '',                    // Empty value
            'invalid' => '',             // Invalid payment method
        ];

        foreach ($testCases as $input => $expected) {
            $testData = $this->sampleTransactionData;
            $testData['paymentMethod'] = $input;
            $this->orderHistory->setTransactionResult($testData);

            $this->assertEquals(
                $expected,
                $this->orderHistory->getPaymentMethod(),
                sprintf("Payment method '%s' should return '%s'", $input, $expected)
            );
        }
    }
}
