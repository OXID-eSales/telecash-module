<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\Application\Model;

use OxidSolutionCatalysts\TeleCash\IPG\TeleCashCurrency;
use OxidSolutionCatalysts\TeleCash\Tests\Unit\Application\Model\TestClasses\TeleCashOrderTestClass;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

/**
 * Unit Test Suite for TeleCashOrder
 *
 * Tests the business logic of the TeleCashOrder class without database dependencies.
 * Focuses on transaction data handling, value conversions and data validation.
 */
class TeleCashOrderTest extends TestCase
{
    private array $sampleTransactionData;
    private TeleCashOrderTestClass $order;
    private $teleCashCurrencyMock;

    /**
     * Set up test environment
     *
     * Initializes a test instance of TeleCashOrder and prepares sample transaction
     * data that represents a typical payment transaction.
     */
    protected function setUp(): void
    {
        // Create mock for TeleCashCurrency
        $this->teleCashCurrencyMock = $this->createMock(TeleCashCurrency::class);

        // Initialize test class without database connection
        $this->order = new TeleCashOrderTestClass(null, false);

        // Set mocked currency handler
        $reflection = new \ReflectionClass($this->order);
        $property = $reflection->getProperty('teleCashCurrency');
        $property->setValue($this->order, $this->teleCashCurrencyMock);

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
            'processor_response_code' => '00',
            'paymentMethod' => 'V'
        ];

        $this->order->setTransactionResult($this->sampleTransactionData);
    }

    /**
     * Test transaction type retrieval and validation
     *
     * Verifies that:
     * - Valid transaction types are correctly returned
     * - Invalid types are handled properly (empty string)
     * - Both DB and TransactionResult sources work
     */
    public function testGetTxnType(): void
    {
        // Test from TransactionResult
        $this->assertEquals('sale', $this->order->getTxnType());

        // Test invalid txntype
        $invalidData = $this->sampleTransactionData;
        $invalidData['txntype'] = 'invalid';
        $this->order->setTransactionResult($invalidData);
        $this->assertEquals('', $this->order->getTxnType());

        // Test from DB (simulated)
        $this->order->simulateLoadedFromDb([
            'txntype' => 'sale'
        ]);
        $this->assertEquals('sale', $this->order->getTxnType());
    }

    /**
     * Test order ID retrieval
     *
     * Verifies the order ID is correctly returned from both
     * transaction data and database
     */
    public function testGetOid(): void
    {
        // Test from TransactionResult
        $this->assertEquals('test123', $this->order->getOid());

        // Test from DB
        $this->order->simulateLoadedFromDb([
            'oid' => 'dbtest123'
        ]);
        $this->assertEquals('dbtest123', $this->order->getOid());
    }

    /**
     * Test IpgTransaction ID retrieval
     *
     * Verifies the order ID is correctly returned from both
     * transaction data and database
     */
    public function testGetIpgTransactionId(): void
    {
        // Test from TransactionResult
        $this->assertEquals('ipg123', $this->order->getIpgTransactionId());
    }

    /**
     * Test currency (in oxid-style) code conversion and validation
     *
     * Verifies that:
     * - Numeric currency codes are correctly converted to ISO codes
     * - Invalid codes are handled properly (empty string)
     * - Both data sources work correctly
     */
    public function testGetOxidCurrency(): void
    {
        // Test 1: Successful case with '978'
        $this->teleCashCurrencyMock
            ->expects($this->exactly(3))
            ->method('getShortnameByCurrencyCode')
            ->willReturnCallback(function ($code) {
                if ($code === '978') {
                    return 'EUR';
                }
                if ($code === 'EUR') {
                    return 'EUR';
                }
                if ($code === 'invalid') {
                    throw new InvalidArgumentException('Invalid currency code');
                }
                return '';
            });

        // Test from TransactionResult with valid currency
        $this->assertEquals('EUR', $this->order->getOxidCurrency());

        // Test invalid currency
        $invalidData = $this->sampleTransactionData;
        $invalidData['currency'] = 'invalid';
        $this->order->setTransactionResult($invalidData);
        $this->assertEquals('', $this->order->getOxidCurrency());

        // Test from DB with valid currency
        $this->order->simulateLoadedFromDb([
            'currency' => 'EUR'
        ]);
        $this->assertEquals('EUR', $this->order->getOxidCurrency());
    }

    /**
     * Test currency code conversion and validation
     *
     * Verifies that:
     * - Numeric currency codes are correctly converted to ISO codes
     * - Invalid codes are handled properly (empty string)
     * - Both data sources work correctly
     */
    public function testGetCurrency(): void
    {
        // Test from TransactionResult
        $this->assertEquals('978', $this->order->getCurrency());

        // Test invalid currency
        $invalidData = $this->sampleTransactionData;
        $invalidData['currency'] = 'invalid';
        $this->order->setTransactionResult($invalidData);
        $this->assertEquals('invalid', $this->order->getCurrency());

        // Test from DB
        $this->order->simulateLoadedFromDb([
            'currency' => '978'
        ]);
        $this->assertEquals('978', $this->order->getCurrency());
    }

    /**
     * Test charge total handling
     *
     * Verifies that:
     * - Valid amounts are correctly converted to float
     * - German decimal separator is handled
     * - Invalid amounts return 0.0
     * - Both data sources work correctly
     */
    public function testGetChargeTotal(): void
    {
        // Test from TransactionResult
        $this->assertEquals('99.99', $this->order->getChargeTotal());

        // Test German decimal separator
        $germanData = $this->sampleTransactionData;
        $germanData['chargetotal'] = '99,99';
        $this->order->setTransactionResult($germanData);
        $this->assertEquals('99.99', $this->order->getChargeTotal());

        // Test invalid charge total
        $invalidData = $this->sampleTransactionData;
        $invalidData['chargetotal'] = 'invalid';
        $this->order->setTransactionResult($invalidData);
        $this->assertEquals('0.00', $this->order->getChargeTotal());

        // Test from DB
        $this->order->simulateLoadedFromDb([
            'chargetotal' => 99.99
        ]);
        $this->assertEquals('99.99', $this->order->getChargeTotal());
    }

    /**
     * Test charge total (in oxid style as float) handling
     *
     * Verifies that:
     * - Valid amounts are correctly converted to float
     * - German decimal separator is handled
     * - Invalid amounts return 0.0
     * - Both data sources work correctly
     */
    public function testGetOxidChargeTotal(): void
    {
        // Test from TransactionResult
        $this->assertEquals(99.99, $this->order->getOxidChargeTotal());

        // Test German decimal separator
        $germanData = $this->sampleTransactionData;
        $germanData['chargetotal'] = '99,99';
        $this->order->setTransactionResult($germanData);
        $this->assertEquals(99.99, $this->order->getOxidChargeTotal());

        // Test invalid charge total
        $invalidData = $this->sampleTransactionData;
        $invalidData['chargetotal'] = 'invalid';
        $this->order->setTransactionResult($invalidData);
        $this->assertEquals(0.0, $this->order->getOxidChargeTotal());

        // Test from DB
        $this->order->simulateLoadedFromDb([
            'chargetotal' => 99.99
        ]);
        $this->assertEquals(99.99, $this->order->getOxidChargeTotal());
    }

    /**
     * Test status retrieval
     *
     * Ensures the transaction status is correctly returned
     * from both data sources
     */
    public function testGetStatus(): void
    {
        // Test from TransactionResult
        $this->assertEquals('APPROVED', $this->order->getStatus());

        // Test from DB
        $this->order->simulateLoadedFromDb([
            'status' => 'DECLINED'
        ]);
        $this->assertEquals('DECLINED', $this->order->getStatus());
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
     * - Both data sources
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

        // Test TransactionResult variants
        foreach ($testCases as $input => $expected) {
            $testData = $this->sampleTransactionData;
            $testData['paymentMethod'] = $input;
            $this->order->setTransactionResult($testData);

            $this->assertEquals(
                $expected,
                $this->order->getPaymentMethod(),
                sprintf("Payment method '%s' should return '%s' from TransactionResult", $input, $expected)
            );
        }

        // Test DB variants
        foreach ($testCases as $input => $expected) {
            $this->order->simulateLoadedFromDb([
                'paymentmethod' => $expected
            ]);

            $this->assertEquals(
                $expected,
                $this->order->getPaymentMethod(),
                sprintf("Payment method '%s' should return '%s' from DB", $input, $expected)
            );
        }
    }
}
