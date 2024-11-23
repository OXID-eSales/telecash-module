<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\TeleCash\Tests\Integration\Application\Model;

use OxidSolutionCatalysts\TeleCash\Application\Model\TeleCashOrder;
use PHPUnit\Framework\TestCase;

/**
 * Integration Test Suite for TeleCashOrder
 *
 * Tests the database interaction of TeleCashOrder, specifically:
 * - Saving transaction data
 * - Loading transaction data
 * - Data consistency through save/load cycle
 */
class TeleCashOrderTest extends TestCase
{
    private TeleCashOrder $order;
    private array $sampleTransactionData;
    private string $testOrderId = 'testOrderId';

    /**
     * Set up test environment
     *
     * Initializes test instance and prepares sample data
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->order = new TeleCashOrder($this->testOrderId);

        $this->sampleTransactionData = [
            'txntype' => 'sale',
            'txndatetime' => '2024-01-15 10:30:00',
            'oid' => 'test123',
            'endpointTransactionId' => 'endpoint123',
            'terminal_id' => 'term123',
            'ipgTransactionId' => 'ipg123',
            'currency' => '978',
            'chargetotal' => '99.99',
            'status' => 'APPROVED',
            'processor_response_code' => '00',
            'paymentMethod' => 'V'
        ];
    }

    /**
     * Test complete save and load cycle
     *
     * Verifies that:
     * - Data is correctly saved to database
     * - Data can be loaded from database
     * - All fields maintain their values and types
     */
    public function testSaveAndLoad(): void
    {
        // Save transaction data
        $this->order->setTransactionResult($this->sampleTransactionData);
        $oxid = $this->order->save();
        $this->assertTrue($oxid !== false, 'Failed to save order data');

        // Load data in new instance
        $loadedOrder = new TeleCashOrder($this->testOrderId);
        $loadResult = $loadedOrder->load($oxid);
        $this->assertTrue($loadResult, 'Failed to load order data');

        // Initialize transaction data from loaded response
        $loadedOrder->loadTransactionResultFromDb();

        // Verify all fields maintain their values
        $this->assertEquals(
            $this->sampleTransactionData['txntype'],
            $loadedOrder->getTxnType(),
            'Transaction type mismatch'
        );
        $this->assertEquals(
            $this->sampleTransactionData['txndatetime'],
            $loadedOrder->getTxnDateTime(),
            'Transaction datetime mismatch'
        );
        $this->assertEquals(
            $this->sampleTransactionData['oid'],
            $loadedOrder->getOid(),
            'Order ID mismatch'
        );
        $this->assertEquals(
            $this->sampleTransactionData['endpointTransactionId'],
            $loadedOrder->getEndpointTransactionId(),
            'Endpoint transaction ID mismatch'
        );
        $this->assertEquals(
            $this->sampleTransactionData['terminal_id'],
            $loadedOrder->getTerminalId(),
            'Terminal ID mismatch'
        );
        $this->assertEquals(
            $this->sampleTransactionData['ipgTransactionId'],
            $loadedOrder->getIpgTransactionId(),
            'IPG transaction ID mismatch'
        );
        $this->assertEquals(
            'EUR',
            $loadedOrder->getCurrency(),
            'Currency mismatch'
        );
        $this->assertEquals(
            (float)$this->sampleTransactionData['chargetotal'],
            $loadedOrder->getChargeTotal(),
            'Charge total mismatch'
        );
        $this->assertEquals(
            $this->sampleTransactionData['status'],
            $loadedOrder->getStatus(),
            'Status mismatch'
        );
        $this->assertEquals(
            $this->sampleTransactionData['processor_response_code'],
            $loadedOrder->getProcessorResponseCode(),
            'Processor response code mismatch'
        );
        $this->assertEquals(
            'cc_visa',
            $loadedOrder->getPaymentMethod(),
            'Payment method mismatch'
        );
    }

    /**
     * Clean up test environment
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
