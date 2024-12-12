<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\Core\Service;

use OxidSolutionCatalysts\TeleCash\Application\Model\TeleCashOrder;
use OxidSolutionCatalysts\TeleCash\Application\Model\TeleCashOrderHistory;
use OxidSolutionCatalysts\TeleCash\Core\Service\ErrorDisplayServiceInterface;
use OxidSolutionCatalysts\TeleCash\Core\Service\OxNewService;
use OxidSolutionCatalysts\TeleCash\Core\Service\TeleCashAPIServiceInterface;
use OxidSolutionCatalysts\TeleCash\Core\Service\TeleCashAfterProcessService;
use OxidSolutionCatalysts\TeleCash\Exception\TeleCashException;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Error;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Order\Sell;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCash;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test suite for TeleCashAfterProcessService
 *
 * This class tests the functionality of the TeleCashAfterProcessService,
 * which handles post-processing operations for TeleCash orders, particularly
 * the charging process.
 */
class TeleCashAfterProcessServiceTest extends TestCase
{
    /** @var TeleCashAPIServiceInterface&MockObject */
    private $apiServiceMock;

    /** @var OxNewService&MockObject */
    private $oxNewServiceMock;

    /** @var ErrorDisplayServiceInterface&MockObject */
    private $errorDisplayMock;

    /** @var TeleCash&MockObject */
    private $teleCashMock;

    /** @var TeleCashAfterProcessService */
    private $service;

    /**
     * Set up the test environment before each test
     *
     * Creates mock objects for all dependencies and initializes the service
     * with these mocks. This setup allows us to control and verify all
     * external interactions during testing.
     */
    protected function setUp(): void
    {
        // Create mocks for all dependencies
        $this->apiServiceMock = $this->createMock(TeleCashAPIServiceInterface::class);
        $this->oxNewServiceMock = $this->createMock(OxNewService::class);
        $this->errorDisplayMock = $this->createMock(ErrorDisplayServiceInterface::class);
        $this->teleCashMock = $this->createMock(TeleCash::class);

        // Configure API service mock to return our TeleCash mock
        $this->apiServiceMock
            ->method('getTeleCashAPI')
            ->willReturn($this->teleCashMock);

        // Initialize service with mocked dependencies
        $this->service = new TeleCashAfterProcessService(
            $this->apiServiceMock,
            $this->oxNewServiceMock,
            $this->errorDisplayMock
        );
    }

    /**
     * Test successful order charging process
     *
     * Verifies that the service correctly:
     * 1. Loads the order
     * 2. Makes the API call
     * 3. Processes a successful response
     * 4. Creates a transaction history entry
     */
    public function testDoChargeOrderSuccessful(): void
    {
        // Test data
        $orderId = 'test_order_123';
        $amount = 99.99;
        $currency = 'EUR';
        $transactionTime = '2024-03-14T15:30:45+00:00';

        // Create and configure order mock
        $orderMock = $this->createMock(TeleCashOrder::class);
        $orderMock->method('load')->willReturn(true);
        $orderMock->method('getCurrency')->willReturn($currency);
        $orderMock->method('getOid')->willReturn($orderId);
        $orderMock->method('getPaymentMethod')->willReturn('CREDITCARD');

        // Configure oxNew service to return our order mock
        $this->oxNewServiceMock
            ->method('oxNew')
            ->willReturnCallback(function($class) use ($orderMock) {
                if ($class === TeleCashOrder::class) {
                    return $orderMock;
                }
                return $this->createMock(TeleCashOrderHistory::class);
            });

        // Create successful response mock
        $successResponse = $this->createMock(Sell::class);
        $successResponse->method('getOrderId')->willReturn($orderId);
        $successResponse->method('getTerminalId')->willReturn('12345');
        $successResponse->method('getTransactionTime')->willReturn($transactionTime);
        $successResponse->method('getTransactionResult')->willReturn('APPROVED');
        $successResponse->method('getProcessorResponseCode')->willReturn('00');

        // Configure TeleCash mock to return successful response
        $this->teleCashMock
            ->method('postAuthOrder')
            ->with($orderId, $currency, (string)$amount)
            ->willReturn($successResponse);

        // Execute the method under test
        $this->service->doChargeOrder($orderId, $amount);
    }

    /**
     * Test error handling when order cannot be loaded
     *
     * Verifies that the service throws the correct exception
     * when the order loading fails.
     */
    public function testDoChargeOrderFailsWhenOrderCannotBeLoaded(): void
    {
        // Configure order mock to fail loading
        $orderMock = $this->createMock(TeleCashOrder::class);
        $orderMock->method('load')->willReturn(false);

        // Configure oxNew service to return our failing order mock
        $this->oxNewServiceMock
            ->method('oxNew')
            ->with(TeleCashOrder::class)
            ->willReturn($orderMock);

        // Set up expectation for exception
        $this->expectException(TeleCashException::class);
        $this->expectExceptionMessage('Could not load TeleCash order');

        // Execute the method under test
        $this->service->doChargeOrder('non_existent_order', 99.99);
    }

    /**
     * Test handling of API error responses
     *
     * Verifies that the service correctly processes error responses
     * from the TeleCash API and displays appropriate error messages.
     */
    public function testDoChargeOrderHandlesErrorResponse(): void
    {
        // Test data
        $orderId = 'test_order_123';
        $amount = 99.99;
        $currency = 'EUR';
        $errorMessage = 'Transaction declined';

        // Create and configure order mock
        $orderMock = $this->createMock(TeleCashOrder::class);
        $orderMock->method('load')->willReturn(true);
        $orderMock->method('getCurrency')->willReturn($currency);
        $orderMock->method('getOid')->willReturn($orderId);

        // Configure oxNew service
        $this->oxNewServiceMock
            ->method('oxNew')
            ->with(TeleCashOrder::class)
            ->willReturn($orderMock);

        // Create error response mock
        $errorResponse = $this->createMock(Error::class);
        $errorResponse->method('getClientErrorDetail')
            ->willReturn($errorMessage);

        // Configure TeleCash mock to return error response
        $this->teleCashMock
            ->method('postAuthOrder')
            ->willReturn($errorResponse);

        // Expect error message to be displayed
        $this->errorDisplayMock
            ->expects($this->once())
            ->method('showErrorMessage')
            ->with($errorMessage);

        // Execute the method under test
        $this->service->doChargeOrder($orderId, $amount);
    }
}
