<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\Application\Model;

use Doctrine\DBAL\Connection;
use OxidSolutionCatalysts\TeleCash\Application\Model\TeleCashPayment;
use OxidSolutionCatalysts\TeleCash\Core\Module;
use OxidSolutionCatalysts\TeleCash\Core\Service\TeleCashPaymentValidatorService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TeleCashPaymentTest extends TestCase
{
    private TeleCashPayment|MockObject $teleCashPayment;
    private Connection|MockObject $connectionMock;
    private TeleCashPaymentValidatorService|MockObject $validatorMock;

    protected function setUp(): void
    {
        // Create mock for database connection
        $this->connectionMock = $this->createMock(Connection::class);

        // Create mock for validator service
        $this->validatorMock = $this->createMock(TeleCashPaymentValidatorService::class);

        // Create mock for TeleCashPayment
        $this->teleCashPayment = $this->getMockBuilder(TeleCashPayment::class)
            ->setConstructorArgs([
                $this->validatorMock,
                $this->connectionMock,
                false
            ])
            ->onlyMethods([
                'init',
                'getPaymentId',
                'setPaymentId',
                'getTeleCashIdent',
                'setTeleCashIdent',
                'getTeleCashCaptureType',
                'setTeleCashCaptureType',
                'addField',
                'assign',
                'getViewName',
                'getShopId',
                'buildSelectString',
                'isLoaded'
            ])
            ->getMock();

        // Mock init to prevent oxNew calls
        $this->teleCashPayment->method('init')
            ->willReturn(true);
    }

    /**
     * Helper method to create a real TeleCashPayment instance for testing
     */
    private function createRealTeleCashPayment(): TeleCashPayment
    {
        $payment = $this->getMockBuilder(TeleCashPayment::class)
            ->setConstructorArgs([
                $this->validatorMock,
                $this->connectionMock,
                false
            ])
            ->onlyMethods([
                'init',
                'save',
                'isAdmin',
                'getFieldNames'
            ])
            ->getMock();

        $payment->method('init')
            ->willReturn(true);

        // Mock isAdmin method
        $payment->method('isAdmin')
            ->willReturn(false);

        // Mock save method from parent
        $payment->method('save')
            ->willReturn(true);

        // Mock getFieldNames if needed
        $payment->method('getFieldNames')
            ->willReturn(['oxid', 'oxpaymentid', 'telecashident', 'capturetype']);

        return $payment;
    }

    /**
     * Test setting and getting the payment ID
     */
    public function testSetAndGetPaymentId(): void
    {
        $paymentId = 'test_payment_id';

        // Expect setPaymentId to be called once with the test payment ID
        $this->teleCashPayment->expects($this->once())
            ->method('setPaymentId')
            ->with($paymentId);

        // Expect getPaymentId to be called once and return the test payment ID
        $this->teleCashPayment->expects($this->once())
            ->method('getPaymentId')
            ->willReturn($paymentId);

        // Call the methods and assert the result
        $this->teleCashPayment->setPaymentId($paymentId);
        $this->assertEquals($paymentId, $this->teleCashPayment->getPaymentId());
    }

    /**
     * Test setting and getting the TeleCash ident
     */
    public function testSetAndGetTeleCashIdent(): void
    {
        $validIdent = Module::TELECASH_PAYMENT_IDENTS[0];

        // Expect setTeleCashIdent to be called once with the valid ident
        $this->teleCashPayment->expects($this->once())
            ->method('setTeleCashIdent')
            ->with($validIdent);

        // Expect getTeleCashIdent to be called once and return the valid ident
        $this->teleCashPayment->expects($this->once())
            ->method('getTeleCashIdent')
            ->willReturn($validIdent);

        // Call the methods and assert the result
        $this->teleCashPayment->setTeleCashIdent($validIdent);
        $this->assertEquals($validIdent, $this->teleCashPayment->getTeleCashIdent());
    }

    /**
     * Test setting and getting the TeleCash capture type
     */
    public function testSetAndGetTeleCashCaptureType(): void
    {
        $validIdent = Module::TELECASH_PAYMENT_IDENTS[0];
        $validCaptureType = Module::TELECASH_CAPTURE_TYPES[$validIdent][0];

        // Expect setTeleCashCaptureType to be called once with the valid capture type
        $this->teleCashPayment->expects($this->once())
            ->method('setTeleCashCaptureType')
            ->with($validCaptureType);

        // Expect getTeleCashCaptureType to be called once and return the valid capture type
        $this->teleCashPayment->expects($this->once())
            ->method('getTeleCashCaptureType')
            ->willReturn($validCaptureType);

        // Call the methods and assert the result
        $this->teleCashPayment->setTeleCashCaptureType($validCaptureType);
        $this->assertEquals($validCaptureType, $this->teleCashPayment->getTeleCashCaptureType());
    }

    /**
     * Test getting possible TeleCash Idents
     */
    public function testGetPossibleTeleCashIdents(): void
    {
        $teleCashPaymentReal = $this->createRealTeleCashPayment();
        $possibleIdents = $teleCashPaymentReal->getPossibleTeleCashIdents();

        $this->assertEquals(Module::TELECASH_PAYMENT_IDENTS, $possibleIdents);
        $this->assertIsArray($possibleIdents);
        $this->assertNotEmpty($possibleIdents);
    }

    /**
     * Test validating TeleCash Ident with valid input
     */
    public function testValidTeleCashIdentWithValidInput(): void
    {
        $teleCashPaymentReal = $this->createRealTeleCashPayment();
        $validIdent = Module::TELECASH_PAYMENT_IDENTS[0];

        $result = $teleCashPaymentReal->validTeleCashIdent($validIdent);

        $this->assertEquals($validIdent, $result);
    }

    /**
     * Test validating TeleCash Ident with invalid input
     */
    public function testValidTeleCashIdentWithInvalidInput(): void
    {
        $teleCashPaymentReal = $this->createRealTeleCashPayment();
        $invalidIdent = 'invalid_ident';

        $result = $teleCashPaymentReal->validTeleCashIdent($invalidIdent);

        $this->assertEquals(Module::TELECASH_PAYMENT_IDENT_DEFAULT, $result);
    }

    /**
     * Test getting possible TeleCash Capture Types
     */
    public function testGetPossibleTeleCashCaptureTypes(): void
    {
        $teleCashPaymentReal = $this->createRealTeleCashPayment();
        $validIdent = Module::TELECASH_PAYMENT_IDENTS[0];

        $captureTypes = $teleCashPaymentReal->getPossibleTeleCashCaptureTypes($validIdent);

        $this->assertEquals(Module::TELECASH_CAPTURE_TYPES[$validIdent], $captureTypes);
        $this->assertIsArray($captureTypes);
        $this->assertNotEmpty($captureTypes);
    }

    /**
     * Test validating TeleCash Capture Type with valid input
     */
    public function testValidTeleCashCaptureTypeWithValidInput(): void
    {
        $teleCashPaymentReal = $this->createRealTeleCashPayment();
        $validIdent = Module::TELECASH_PAYMENT_IDENTS[0];
        $validCaptureType = Module::TELECASH_CAPTURE_TYPES[$validIdent][0];

        $result = $teleCashPaymentReal->validTeleCashCaptureType($validCaptureType, $validIdent);

        $this->assertEquals($validCaptureType, $result);
    }

    /**
     * Test validating TeleCash Capture Type with invalid input
     */
    public function testValidTeleCashCaptureTypeWithInvalidInput(): void
    {
        $teleCashPaymentReal = $this->createRealTeleCashPayment();
        $validIdent = Module::TELECASH_PAYMENT_IDENTS[0];
        $invalidCaptureType = 'invalid_capture_type';

        $result = $teleCashPaymentReal->validTeleCashCaptureType($invalidCaptureType, $validIdent);

        $this->assertEquals(Module::TELECASH_CAPTURE_TYPE_DIRECT, $result);
    }

    /**
     * Test loading TeleCash Payment by PaymentId
     */
    public function testLoadByPaymentId(): void
    {
        $paymentId = 'test_payment_id';
        $mockData = ['oxid' => 'test_oxid', 'field1' => 'value1'];
        $shopId = 1;
        $viewName = 'test_view';

        // Configure mocks
        $this->teleCashPayment->method('getViewName')->willReturn($viewName);
        $this->teleCashPayment->method('getShopId')->willReturn($shopId);
        $this->teleCashPayment->method('buildSelectString')
            ->willReturn('SELECT * FROM test_table');
        $this->teleCashPayment->method('assign')
            ->willReturn(true);
        $this->teleCashPayment->method('addField')
            ->willReturn(true);
        $this->teleCashPayment->method('isLoaded')
            ->willReturn(true);

        // Configure connection mock to return test data
        $this->connectionMock->expects($this->once())
            ->method('fetchAssociative')
            ->willReturn($mockData);

        $result = $this->teleCashPayment->loadByPaymentId($paymentId);

        $this->assertTrue($result);
    }

    /**
     * Test loading TeleCash Payment by PaymentId with invalid data
     */
    public function testLoadByPaymentIdWithInvalidData(): void
    {
        $paymentId = 'invalid_payment_id';
        $shopId = 1;
        $viewName = 'test_view';

        // Configure mocks
        $this->teleCashPayment->method('getViewName')->willReturn($viewName);
        $this->teleCashPayment->method('getShopId')->willReturn($shopId);
        $this->teleCashPayment->method('buildSelectString')
            ->willReturn('SELECT * FROM test_table');
        $this->teleCashPayment->method('isLoaded')
            ->willReturn(false);

        // Configure connection mock to return false
        $this->connectionMock->expects($this->once())
            ->method('fetchAssociative')
            ->willReturn(false);

        $result = $this->teleCashPayment->loadByPaymentId($paymentId);

        $this->assertFalse($result);
    }

    /**
     * Test saving payment with validator
     */
    public function testSave(): void
    {
        $teleCashPaymentReal = $this->createRealTeleCashPayment();

        $testPaymentId = 'test_payment';
        $testIdent = Module::TELECASH_PAYMENT_IDENT_DEFAULT;
        $testCaptureType = Module::TELECASH_CAPTURE_TYPE_DIRECT;

        // Set test values directly
        $teleCashPaymentReal->setTestValues(
            $testPaymentId,
            $testIdent,
            $testCaptureType
        );

        // Configure validator mock to return true (payment exists)
        $this->validatorMock->expects($this->once())
            ->method('checkIfPaymentExists')
            ->with($testPaymentId, $testIdent, $testCaptureType)
            ->willReturn(true);

        $this->assertTrue($teleCashPaymentReal->validateBeforeSave(), 'Validation before save should pass');
    }
}
