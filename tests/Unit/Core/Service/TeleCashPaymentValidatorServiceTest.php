<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\Core\Service;

use Doctrine\DBAL\Driver\ResultStatement;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use Doctrine\DBAL\Query\QueryBuilder;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\TableViewNameGenerator;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidSolutionCatalysts\TeleCash\Exception\TeleCashException;
use OxidSolutionCatalysts\TeleCash\Core\Module;
use OxidSolutionCatalysts\TeleCash\Core\Service\OxNewService;
use OxidSolutionCatalysts\TeleCash\Core\Service\RegistryService;
use OxidSolutionCatalysts\TeleCash\Core\Service\TeleCashPaymentValidatorService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for TeleCashPaymentValidatorService
 *
 * Tests the validation of TeleCash payments by checking their existence
 * in the database and handling various scenarios including database errors
 */
class TeleCashPaymentValidatorServiceTest extends TestCase
{
    /** @var TeleCashPaymentValidatorService Service under test */
    private TeleCashPaymentValidatorService $service;

    /** @var MockObject Mock for OxNewService */
    private MockObject $oxNewService;

    /** @var MockObject Mock for RegistryService */
    private MockObject $registryService;

    /** @var MockObject Mock for QueryBuilderFactory */
    private MockObject $queryBuilderFactory;

    /** @var MockObject Mock for QueryBuilder */
    private MockObject $queryBuilder;

    /** @var MockObject Mock for ExpressionBuilder */
    private MockObject $expressionBuilder;

    /** @var MockObject Mock for Config */
    private MockObject $config;

    /** @var MockObject Mock for TableViewNameGenerator */
    private MockObject $tableViewNameGenerator;

    /** @var MockObject Mock for ResultStatement */
    private MockObject $resultStatement;

    /**
     * Set up test environment before each test
     *
     * Creates all necessary mocks and instantiates the service under test
     */
    protected function setUp(): void
    {
        // Create mock objects for all dependencies
        $this->oxNewService = $this->createMock(OxNewService::class);
        $this->registryService = $this->createMock(RegistryService::class);
        $this->queryBuilderFactory = $this->createMock(QueryBuilderFactoryInterface::class);
        $this->queryBuilder = $this->createMock(QueryBuilder::class);
        $this->expressionBuilder = $this->createMock(ExpressionBuilder::class);
        $this->config = $this->createMock(Config::class);
        $this->tableViewNameGenerator = $this->createMock(TableViewNameGenerator::class);

        // Special setup for ResultStatement to include fetchOne method
        $this->resultStatement = $this->getMockBuilder(ResultStatement::class)
            ->addMethods(['fetchOne'])
            ->getMockForAbstractClass();

        // Create service instance with mocked dependencies
        $this->service = new TeleCashPaymentValidatorService(
            $this->oxNewService,
            $this->registryService,
            $this->queryBuilderFactory
        );
    }

    /**
     * Test case: Payment does not exist in database
     *
     * Expects the service to return true when no payment record is found
     */
    public function testCheckIfPaymentExistsReturnsTrueWhenPaymentDoesNotExist(): void
    {
        $paymentId = 'testPaymentId';
        $ident = 'testIdent';
        $captureType = 'testCaptureType';
        $shopId = '1';
        $viewName = 'oxv_osc_telecash_payment';

        $this->setupMocks($viewName, $shopId, '');

        $result = $this->service->checkIfPaymentExists($paymentId, $ident, $captureType);

        $this->assertTrue($result);
    }

    /**
     * Test case: Payment exists in database
     *
     * Expects the service to return false when a payment record is found
     */
    public function testCheckIfPaymentExistsReturnsFalseWhenPaymentExists(): void
    {
        $paymentId = 'testPaymentId';
        $ident = 'testIdent';
        $captureType = 'testCaptureType';
        $shopId = '1';
        $viewName = 'oxv_osc_telecash_payment';

        $this->setupMocks($viewName, $shopId, 'existingOxid');

        $result = $this->service->checkIfPaymentExists($paymentId, $ident, $captureType);

        $this->assertFalse($result);
    }

    /**
     * Test case: Database error occurs
     *
     * Expects the service to throw a TeleCashException when a database error occurs
     */
    public function testCheckIfPaymentExistsThrowsExceptionOnDatabaseError(): void
    {
        $paymentId = 'testPaymentId';
        $ident = 'testIdent';
        $captureType = 'testCaptureType';
        $viewName = 'oxv_osc_telecash_payment';

        // Setup minimal mocks needed for the error case
        $this->oxNewService->expects($this->once())
            ->method('oxNew')
            ->with(TableViewNameGenerator::class)
            ->willReturn($this->tableViewNameGenerator);

        $this->tableViewNameGenerator->expects($this->once())
            ->method('getViewName')
            ->with(Module::TELECASH_PAYMENT_EXTENSION_TABLE)
            ->willReturn($viewName);

        $this->queryBuilderFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->queryBuilder);

        $this->queryBuilder->expects($this->any())
            ->method('expr')
            ->willReturn($this->expressionBuilder);

        // Simulate database error
        $this->queryBuilder->expects($this->once())
            ->method('select')
            ->willThrowException(new Exception('Database error'));

        $this->expectException(TeleCashException::class);

        $this->service->checkIfPaymentExists($paymentId, $ident, $captureType);
    }

    /**
     * Helper method to setup common mocks for tests
     *
     * Sets up all necessary mock objects and their expected behavior
     * for the database query execution
     *
     * @param string $viewName The expected view name
     * @param string $shopId The shop ID to be used
     * @param string $returnValue The value to be returned by the database query
     */
    private function setupMocks(string $viewName, string $shopId, string $returnValue): void
    {
        // Setup TableViewNameGenerator
        $this->oxNewService->expects($this->once())
            ->method('oxNew')
            ->with(TableViewNameGenerator::class)
            ->willReturn($this->tableViewNameGenerator);

        $this->tableViewNameGenerator->expects($this->once())
            ->method('getViewName')
            ->with(Module::TELECASH_PAYMENT_EXTENSION_TABLE)
            ->willReturn($viewName);

        // Setup QueryBuilder and its chain methods
        $this->queryBuilderFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->queryBuilder);

        $this->queryBuilder->expects($this->any())
            ->method('expr')
            ->willReturn($this->expressionBuilder);

        $this->queryBuilder->method('select')->willReturnSelf();
        $this->queryBuilder->method('from')->willReturnSelf();
        $this->queryBuilder->method('where')->willReturnSelf();
        $this->queryBuilder->method('andWhere')->willReturnSelf();
        $this->queryBuilder->method('setMaxResults')->willReturnSelf();
        $this->queryBuilder->method('setParameters')->willReturnSelf();

        $this->expressionBuilder->method('eq')->willReturn('1 = 1');

        // Setup Config
        $this->registryService->expects($this->once())
            ->method('getConfig')
            ->willReturn($this->config);

        $this->config->expects($this->once())
            ->method('getShopId')
            ->willReturn($shopId);

        // Setup database result
        $this->queryBuilder->expects($this->once())
            ->method('execute')
            ->willReturn($this->resultStatement);

        $this->resultStatement->expects($this->once())
            ->method('fetchOne')
            ->willReturn($returnValue);
    }
}
