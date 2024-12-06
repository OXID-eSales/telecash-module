<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\TeleCash\Tests\Integration\Application\Model;

use DateTime;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionProviderInterface;
use OxidSolutionCatalysts\TeleCash\Application\Model\TeleCashOrder;
use OxidSolutionCatalysts\TeleCash\Core\Module;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Integration Test for TeleCashOrder
 *
 * Tests the complete workflow with actual database interaction including:
 * - Order creation and storage
 * - Data retrieval
 * - History integration
 * - Field value persistence
 */
class TeleCashOrderTest extends TestCase
{
    /**
     * Sample transaction data for testing
     *
     * Contains a complete set of payment transaction data:
     * - Transaction type (sale)
     * - DateTime information
     * - Order identification
     * - Payment details
     * - Amount and currency
     *
     * @var array
     */
    private array $sampleTransactionData;

    /**
     * Test identifier for database operations
     *
     * @var string
     */
    private string $testOrderId = 'testOrderId';

    protected function setUp(): void
    {
        parent::setUp();

        // Clean up any potential leftover test data
        $this->cleanupTestData();

        // Prepare sample data
        $this->sampleTransactionData = [
            'txntype' => 'sale',
            'txndatetime' => '2024-01-15 10:30:00',
            'oid' => $this->testOrderId,
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
     * Tests the complete save and load cycle
     *
     * Verifies that:
     * - Order can be saved to database
     * - Order can be retrieved using orderId
     * - All fields maintain their values and types
     * - Currency codes are converted correctly
     * - Payment methods are mapped properly
     *
     * @return void
     */
    public function testSaveAndLoadByOrderId(): void
    {
        // Initialize test instance
        $container = ContainerFactory::getInstance()->getContainer();
        $connection = $container->get(ConnectionProviderInterface::class)->get();
        $order = new TeleCashOrder($connection);

        // Set data and save
        $order->setOxOrderId($this->testOrderId);
        $order->setTransactionResult($this->sampleTransactionData);
        $oxid = $order->save();

        $this->assertTrue($oxid !== false, 'Failed to save order data');

        // Load data in new instance
        $loadedOrder = new TeleCashOrder();
        $loadResult = $loadedOrder->loadByOrderId($this->testOrderId);
        $this->assertTrue($loadResult, 'Failed to load order data');

        // Verify fields maintain their values
        $this->assertEquals(
            $this->testOrderId,
            $loadedOrder->getOid(),
            'Order ID mismatch'
        );

        $this->assertEquals(
            $this->sampleTransactionData['txntype'],
            $loadedOrder->getTxnType(),
            'Transaction type mismatch'
        );

        $this->assertEquals(
            'EUR',
            $loadedOrder->getCurrency(),
            'Currency mismatch'
        );

        $this->assertEquals(
            (float)$this->sampleTransactionData['chargetotal'],
            $loadedOrder->getOxidChargeTotal(),
            'Charge total mismatch'
        );

        $this->assertEquals(
            $this->sampleTransactionData['status'],
            $loadedOrder->getStatus(),
            'Status mismatch'
        );

        $this->assertEquals(
            'cc_visa',
            $loadedOrder->getPaymentMethod(),
            'Payment method mismatch'
        );
    }

    /**
     * Tests integration with order history
     *
     * Verifies that:
     * - History entries are created automatically
     * - History data matches order data
     * - History can be retrieved correctly
     * - History list contains expected number of entries
     *
     * @return void
     */
    public function testOrderHistoryIntegration(): void
    {
        // Initialize test instance
        $container = ContainerFactory::getInstance()->getContainer();
        $connection = $container->get(ConnectionProviderInterface::class)->get();
        $order = new TeleCashOrder($connection);

        // Save initial order
        $order->setOxOrderId($this->testOrderId);
        $order->setTransactionResult($this->sampleTransactionData);
        $order->save();

        // Get history directly without saving again
        $historyList = $order->getTeleCashOrderHistoryList();

        $this->assertNotNull($historyList, 'History list should not be null');
        $this->assertEquals(1, $historyList->count(), 'Should have one history entry');

        // Get the first history entry
        $historyList->getTeleCashOrderHistoryList($this->testOrderId);
        $this->assertTrue($historyList->count() > 0, 'History list should not be empty');

        /** @var TeleCashOrderHistory $firstHistory */
        $firstHistory = $historyList->current();
        $this->assertNotNull($firstHistory);
        $this->assertEquals($this->testOrderId, $firstHistory->getOid());
        $this->assertEquals('cc_visa', $firstHistory->getPaymentMethod());
    }

    protected function tearDown(): void
    {
        $this->cleanupTestData();
        parent::tearDown();
    }

    // Helper method to clean up test data from both tables
    // Important: Respects foreign key constraints by deleting history first
    private function cleanupTestData(): void
    {
        $container = ContainerFactory::getInstance()->getContainer();
        try {
            $connection = $container->get(ConnectionProviderInterface::class)->get();

            // Delete from history table first (due to foreign key)
            $connection->executeStatement(
                'DELETE FROM ' . Module::TELECASH_ORDER_HISTORY_EXTENSION_TABLE . ' WHERE ' .
                'oid = ?',
                [$this->testOrderId]
            );

            // Then delete from order table
            $connection->executeStatement(
                'DELETE FROM ' . Module::TELECASH_ORDER_EXTENSION_TABLE . ' WHERE ' .
                'oxorderid = ?',
                [$this->testOrderId]
            );
        } catch (NotFoundExceptionInterface | ContainerExceptionInterface) {
            // nothing todo
        }
    }
}
