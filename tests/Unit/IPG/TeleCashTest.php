<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\IPG;

use DOMDocument;
use DOMException;
use OxidSolutionCatalysts\TeleCash\Core\Service\Logger;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCash;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action\Validation;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action\ConfirmRecurring;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Order\Sell;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Error;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashConstants;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

class TeleCashTest extends TestCase
{
    private TeleCash $teleCash;
    private $orderServiceMock;
    private $loggerMock;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->orderServiceMock = $this->createMock(OrderService::class);
        $this->loggerMock = $this->createMock(Logger::class);

        $this->teleCash = new TeleCash(
            'https://test.com',
            'user',
            'pass',
            '/path/to/cert',
            '/path/to/key',
            'passphrase',
            '/path/to/server/cert',
            $this->loggerMock
        );

        $reflection = new ReflectionClass($this->teleCash);
        $property = $reflection->getProperty('myService');
        $property->setValue($this->teleCash, $this->orderServiceMock);
    }

    private function createSuccessfulResponseXML(string $responseType = 'IPGApiActionResponse'): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
        <SOAP-ENV:Envelope 
            xmlns:SOAP-ENV="' . TeleCashConstants::NAMESPACE_SOAP . '"
            xmlns:ns1="' . TeleCashConstants::NAMESPACE_V1 . '"
            xmlns:ns2="' . TeleCashConstants::NAMESPACE_A1 . '"
            xmlns:ns3="' . TeleCashConstants::NAMESPACE_IPGAPI . '">
            <SOAP-ENV:Body>
                <ns3:' . $responseType . '>
                    <ns3:ApprovalCode>123456</ns3:ApprovalCode>
                    <ns3:AVSResponse>X</ns3:AVSResponse>                    
                    <ns3:Brand>VISA</ns3:Brand>
                    <ns3:OrderId>TEST-1234</ns3:OrderId>
                    <ns3:PaymentType>CREDITCARD</ns3:PaymentType>
                    <ns3:ProcessorApprovalCode>OK123</ns3:ProcessorApprovalCode>
                    <ns3:ProcessorReceiptNumber>7890</ns3:ProcessorReceiptNumber>
                    <ns3:ProcessorReferenceNumber>REF123</ns3:ProcessorReferenceNumber>
                    <ns3:ProcessorResponseMessage>' . Sell::RESPONSE_SUCCESS . '</ns3:ProcessorResponseMessage>
                    <ns3:ProcessorResponseCode>00</ns3:ProcessorResponseCode>
                    <ns3:ProcessorTraceNumber>123ABC</ns3:ProcessorTraceNumber>
                    <ns3:CommercialServiceProvider>MyBank</ns3:CommercialServiceProvider>
                    <ns3:TDate>1234567890</ns3:TDate>
                    <ns3:TerminalID>TID001</ns3:TerminalID>
                    <ns3:TransactionTime>2023-04-15T14:30:00</ns3:TransactionTime>
                    <ns3:TransactionResult>' . Sell::TRANSACTION_RESULT_APPROVED . '</ns3:TransactionResult>
                    <ns3:successfully>true</ns3:successfully>
                </ns3:' . $responseType . '>
            </SOAP-ENV:Body>
        </SOAP-ENV:Envelope>';
    }

    private function createUnsuccessfulResponseXML(): string
    {
        return '<SOAP-ENV:Envelope 
            xmlns:SOAP-ENV="' . TeleCashConstants::NAMESPACE_SOAP . '"
            xmlns:ns1="' . TeleCashConstants::NAMESPACE_V1 . '"
            xmlns:ns2="' . TeleCashConstants::NAMESPACE_A1 . '"
            xmlns:ns3="' . TeleCashConstants::NAMESPACE_IPGAPI . '">
            <SOAP-ENV:Body>
                <ns3:IPGApiActionResponse>
                    <ns3:successfully>false</ns3:successfully>
                    <ns3:ErrorCode>123</ns3:ErrorCode>
                    <ns2:Error>
                        <ns2:ErrorMessage>Error occurred</ns2:ErrorMessage>
                    </ns2:Error>
                </ns3:IPGApiActionResponse>
            </SOAP-ENV:Body>
        </SOAP-ENV:Envelope>';
    }

    /**
     * @throws \Exception
     */
    public function testInstallRecurringPayment(): void
    {
        $domDocument = new DOMDocument();
        $domDocument->loadXML($this->createSuccessfulResponseXML());

        $this->orderServiceMock->expects($this->once())
            ->method('IPGApiAction')
            ->willReturn($domDocument);

        $result = $this->teleCash->installRecurringPayment(
            'hosted_data_id',
            100.00,
            new \DateTime(),
            12,
            1,
            'MONTH'
        );

        $this->assertInstanceOf(Sell::class, $result);
        $this->assertTrue($result->wasSuccessful());
    }

    /**
     * @throws \Exception
     */
    public function testInstallOneTimeRecurringPayment(): void
    {
        $domDocument = new DOMDocument();
        $domDocument->loadXML($this->createSuccessfulResponseXML());

        $this->orderServiceMock->expects($this->once())
            ->method('IPGApiAction')
            ->willReturn($domDocument);

        $result = $this->teleCash->installOneTimeRecurringPayment(
            'hosted_data_id',
            100.00
        );

        $this->assertInstanceOf(Sell::class, $result);
        $this->assertTrue($result->wasSuccessful());
    }

    /**
     * @throws \Exception
     */
    public function testModifyRecurringPayment(): void
    {
        $domDocument = new DOMDocument();
        $domDocument->loadXML($this->createSuccessfulResponseXML());

        $this->orderServiceMock->expects($this->once())
            ->method('IPGApiAction')
            ->willReturn($domDocument);

        $result = $this->teleCash->modifyRecurringPayment(
            'order_id',
            'hosted_data_id',
            100.00,
            new \DateTime(),
            12,
            1,
            'MONTH'
        );

        $this->assertInstanceOf(ConfirmRecurring::class, $result);
        $this->assertTrue($result->wasSuccessful());
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function testRecurringPaymentWithError(): void
    {
        $mockError = $this->createMock(Error::class);

        $this->orderServiceMock->expects($this->once())
            ->method('IPGApiAction')
            ->willReturn($mockError);

        $result = $this->teleCash->modifyRecurringPayment(
            'order_id',
            'hosted_data_id',
            100.00,
            new \DateTime(),
            12,
            1,
            'MONTH'
        );

        $this->assertInstanceOf(Error::class, $result);
    }

    /**
     * @throws \Exception
     */
    public function testCancelRecurringPayment(): void
    {
        $domDocument = new DOMDocument();
        $domDocument->loadXML($this->createSuccessfulResponseXML());

        $this->orderServiceMock->expects($this->once())
            ->method('IPGApiAction')
            ->willReturn($domDocument);

        $result = $this->teleCash->cancelRecurringPayment('order_id');

        $this->assertInstanceOf(ConfirmRecurring::class, $result);
        $this->assertTrue($result->wasSuccessful());
    }


    /**
     * @throws ReflectionException
     */
    public function testGetService(): void
    {
        $reflection = new ReflectionClass(TeleCash::class);
        $method = $reflection->getMethod('getService');

        $result = $method->invoke($this->teleCash);

        $this->assertInstanceOf(OrderService::class, $result);
    }


    /**
     * @throws ReflectionException
     */
    public function testGetServiceWithNull(): void
    {
        $reflection = new ReflectionClass(TeleCash::class);
        $attribute = $reflection->getProperty('myService');
        $attribute->setValue($this->teleCash, null);

        $method = $reflection->getMethod('getService');

        $result = $method->invoke($this->teleCash);
        $this->assertInstanceOf(OrderService::class, $result);
    }

    /**
     * @throws DOMException
     */
    public function testSendEMailNotification(): void
    {
        $domDocument = new DOMDocument();
        $domDocument->loadXML($this->createSuccessfulResponseXML());

        $this->orderServiceMock->expects($this->once())
            ->method('IPGApiAction')
            ->willReturn($domDocument);

        $result = $this->teleCash->sendEMailNotification('order_id', '', null);

        $this->assertInstanceOf(Validation::class, $result);
        $this->assertTrue($result->wasSuccessful());
    }

    /**
     * @throws DOMException
     */
    public function testGetLastTransactions(): void
    {
        $domDocument = new DOMDocument();
        $domDocument->loadXML($this->createSuccessfulResponseXML());

        $this->orderServiceMock->expects($this->once())
            ->method('IPGApiAction')
            ->willReturn($domDocument);

        $result = $this->teleCash->getLastTransactions(10, '123');

        $this->assertInstanceOf(Validation::class, $result);
    }

    /**
     * @throws DOMException
     */
    public function testGetLastOrders(): void
    {
        $domDocument = new DOMDocument();
        $domDocument->loadXML($this->createSuccessfulResponseXML());

        $this->orderServiceMock->expects($this->once())
            ->method('IPGApiAction')
            ->willReturn($domDocument);

        $result = $this->teleCash->getLastOrders(10, '123');

        $this->assertInstanceOf(Validation::class, $result);
    }

    /**
     * @throws DOMException
     */
    public function testGetInquiryByTransactionId(): void
    {
        $domDocument = new DOMDocument();
        $domDocument->loadXML($this->createSuccessfulResponseXML());

        $this->orderServiceMock->expects($this->once())
            ->method('IPGApiAction')
            ->willReturn($domDocument);

        $result = $this->teleCash->getInquiryByIPGTransactionId('123');

        $this->assertInstanceOf(Validation::class, $result);
    }

    /**
     * @throws DOMException
     */
    public function testGetInquiryByOrderIdAndTDate(): void
    {
        $domDocument = new DOMDocument();
        $domDocument->loadXML($this->createSuccessfulResponseXML());

        $this->orderServiceMock->expects($this->once())
            ->method('IPGApiAction')
            ->willReturn($domDocument);

        $result = $this->teleCash->getInquiryByOrderIdAndTDate('123', '123');

        $this->assertInstanceOf(Validation::class, $result);
    }
}
