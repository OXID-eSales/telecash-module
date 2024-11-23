<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\Application\Model;

use OxidSolutionCatalysts\TeleCash\Tests\Unit\Application\Model\TestClasses\TeleCashOrderTestClass;
use PHPUnit\Framework\TestCase;

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

    /**
     * Set up test environment
     *
     * Initializes a test instance of TeleCashOrder and prepares sample transaction
     * data that represents a typical payment transaction.
     */
    protected function setUp(): void
    {
        // Initialize test class without database connection
        $this->order = new TeleCashOrderTestClass('testOrderId');

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

        $this->order->setTransactionResult($this->sampleTransactionData);
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
        $this->assertEquals('sale', $this->order->getTxnType());

        // Test invalid txntype
        $invalidData = $this->sampleTransactionData;
        $invalidData['txntype'] = 'invalid';
        $this->order->setTransactionResult($invalidData);
        $this->assertEquals('', $this->order->getTxnType());
    }

    /**
     * Test transaction datetime retrieval
     *
     * Ensures the datetime string is properly returned without modification
     */
    public function testGetTxnDateTime(): void
    {
        $this->assertEquals('2024-01-15 10:30:00', $this->order->getTxnDateTime());
    }

    /**
     * Test order ID retrieval
     *
     * Verifies the original order ID is correctly returned
     */
    public function testGetOid(): void
    {
        $this->assertEquals('test123', $this->order->getOid());
    }

    /**
     * Test endpoint transaction ID retrieval
     *
     * Ensures the unique transaction identifier is properly returned
     */
    public function testGetEndpointTransactionId(): void
    {
        $this->assertEquals('endpoint123', $this->order->getEndpointTransactionId());
    }

    /**
     * Test terminal ID retrieval
     *
     * Verifies the correct terminal identification is returned
     */
    public function testGetTerminalId(): void
    {
        $this->assertEquals('term123', $this->order->getTerminalId());
    }

    /**
     * Test IPG transaction ID retrieval
     *
     * Ensures the correct IPG transaction identifier is returned
     */
    public function testGetIpgTransactionId(): void
    {
        $this->assertEquals('ipg123', $this->order->getIpgTransactionId());
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
        $this->assertEquals('EUR', $this->order->getCurrency());

        // Test invalid currency
        $invalidData = $this->sampleTransactionData;
        $invalidData['currency'] = 'invalid';
        $this->order->setTransactionResult($invalidData);
        $this->assertEquals('', $this->order->getCurrency());
    }

    /**
     * Test charge total handling
     *
     * Verifies that:
     * - Valid amounts are correctly converted to float
     * - Invalid amounts are handled properly (0.0)
     */
    public function testGetChargeTotal(): void
    {
        $this->assertEquals(99.99, $this->order->getChargeTotal());

        // Test invalid charge total
        $invalidData = $this->sampleTransactionData;
        $invalidData['chargetotal'] = 'invalid';
        $this->order->setTransactionResult($invalidData);
        $this->assertEquals(0.0, $this->order->getChargeTotal());
    }

    /**
     * Test status retrieval
     *
     * Ensures the transaction status is correctly returned
     */
    public function testGetStatus(): void
    {
        $this->assertEquals('APPROVED', $this->order->getStatus());
    }

    /**
     * Test processor response code handling
     *
     * Verifies that the response code is returned exactly as received,
     * preserving leading zeros
     */
    public function testGetProcessorResponseCode(): void
    {
        $this->assertEquals('00', $this->order->getProcessorResponseCode());
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
            $this->order->setTransactionResult($testData);

            $this->assertEquals(
                $expected,
                $this->order->getPaymentMethod(),
                sprintf("Payment method '%s' should return '%s'", $input, $expected)
            );
        }
    }
}
