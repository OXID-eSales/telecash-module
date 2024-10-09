<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\IPG\API;

use OxidSolutionCatalysts\TeleCash\IPG\TeleCash;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action\Validation;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action\Confirm;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action\Display;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Order\Sell;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action\ConfirmRecurring;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Error;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class TeleCashTest extends TestCase
{
    private $teleCash;
    private $orderServiceMock;

    protected function setUp(): void
    {
        $this->orderServiceMock = $this->createMock(OrderService::class);

        $this->teleCash = new TeleCash(
            'https://test.com',
            'user',
            'pass',
            '/path/to/cert',
            '/path/to/key',
            'passphrase',
            '/path/to/server/cert'
        );

        $reflection = new \ReflectionClass($this->teleCash);
        $property = $reflection->getProperty('myService');
        $property->setAccessible(true);
        $property->setValue($this->teleCash, $this->orderServiceMock);
    }

    private function createSuccessfulResponseXML(
        string $responseType = 'IPGApiActionResponse',
        string $withCardNumber = ''
    ): string {
        $cardNumber = '';
        if (!empty($withCardNumber)) {
            $cardNumber = '<ns3:DataStorageItem>
<ns2:CreditCardData>
<ns1:CardNumber>' . $withCardNumber . '</ns1:CardNumber>
<ns1:ExpMonth>12</ns1:ExpMonth>
<ns1:ExpYear>27</ns1:ExpYear>
</ns2:CreditCardData>
<ns2:HostedDataID>
d56feaaf-2d96-4159-8fd6-887e07fc9052
</ns2:HostedDataID>
</ns3:DataStorageItem>';
        }

        $xml = '<SOAP-ENV:Envelope 
            xmlns:SOAP-ENV="' . OrderService::NAMESPACE_SOAP . '"
            xmlns:ns1="' . OrderService::NAMESPACE_N1 . '"
            xmlns:ns2="' . OrderService::NAMESPACE_N2 . '"
            xmlns:ns3="' . OrderService::NAMESPACE_N3 . '">
            <SOAP-ENV:Body>
                ' . $cardNumber . '
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

        return $xml;
    }

    public function testSetDebugMode()
    {
        $this->teleCash->setDebugMode(true);
        $this->assertTrue(true);
    }

    public function testValidate()
    {
        $domDocument = new \DOMDocument();
        $domDocument->loadXML($this->createSuccessfulResponseXML());

        $this->orderServiceMock->expects($this->once())
            ->method('IPGApiAction')
            ->willReturn($domDocument);

        $result = $this->teleCash->validate('4111111111111111', '12/25', 1.0);

        $this->assertInstanceOf(Validation::class, $result);
        $this->assertTrue($result->wasSuccessful());
    }

    public function testStoreHostedData()
    {
        $domDocument = new \DOMDocument();
        $domDocument->loadXML($this->createSuccessfulResponseXML());

        $this->orderServiceMock->expects($this->once())
            ->method('IPGApiAction')
            ->willReturn($domDocument);

        $result = $this->teleCash->storeHostedData('4111111111111111', '12/25', 'hosted_data_id');

        $this->assertInstanceOf(Confirm::class, $result);
        $this->assertTrue($result->wasSuccessful());
    }

    public function testDisplayHostedData()
    {
        $domDocument = new \DOMDocument();
        $domDocument->loadXML($this->createSuccessfulResponseXML('IPGApiActionResponse', '411111******1111'));

        $this->orderServiceMock->expects($this->once())
            ->method('IPGApiAction')
            ->willReturn($domDocument);

        $result = $this->teleCash->displayHostedData('hosted_data_id');

        $this->assertInstanceOf(Display::class, $result);
        $this->assertTrue($result->wasSuccessful());
        $this->assertEquals('411111******1111', $result->getCCNumber());
    }


    public function testValidateHostedData()
    {
        $domDocument = new \DOMDocument();
        $domDocument->loadXML($this->createSuccessfulResponseXML('IPGApiActionResponse', '411111******1111'));

        $this->orderServiceMock->expects($this->once())
            ->method('IPGApiAction')
            ->willReturn($domDocument);

        $result = $this->teleCash->validateHostedData('hosted_data_id');

        $this->assertInstanceOf(Validation::class, $result);
        $this->assertTrue($result->wasSuccessful());
    }

    public function testDeleteHostedData()
    {
        $domDocument = new \DOMDocument();
        $domDocument->loadXML($this->createSuccessfulResponseXML('IPGApiActionResponse', '411111******1111'));

        $this->orderServiceMock->expects($this->once())
            ->method('IPGApiAction')
            ->willReturn($domDocument);

        $result = $this->teleCash->deleteHostedData('hosted_data_id');

        $this->assertInstanceOf(Confirm::class, $result);
        $this->assertTrue($result->wasSuccessful());
    }

    public function testSellUsingHostedData()
    {
        $domDocument = new \DOMDocument();
        $domDocument->loadXML($this->createSuccessfulResponseXML());

        $this->orderServiceMock->expects($this->once())
            ->method('IPGApiOrder')
            ->willReturn($domDocument);

        $result = $this->teleCash->sellUsingHostedData('hosted_data_id', 100.00);

        $this->assertInstanceOf(Sell::class, $result);
        $this->assertTrue($result->wasSuccessful());
    }

    public function testSellUsingHostedDataWithComment()
    {
        $domDocument = new \DOMDocument();
        $domDocument->loadXML($this->createSuccessfulResponseXML());

        $this->orderServiceMock->expects($this->once())
            ->method('IPGApiOrder')
            ->willReturn($domDocument);

        $result = $this->teleCash->sellUsingHostedData(
            'hosted_data_id',
            100.00,
            'Comment'
        );

        $this->assertInstanceOf(Sell::class, $result);
        $this->assertTrue($result->wasSuccessful());
    }

    public function testInstallRecurringPayment()
    {
        $domDocument = new \DOMDocument();
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

    public function testInstallOneTimeRecurringPayment()
    {
        $domDocument = new \DOMDocument();
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

    public function testModifyRecurringPayment()
    {
        $domDocument = new \DOMDocument();
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

    public function testCancelRecurringPayment()
    {
        $domDocument = new \DOMDocument();
        $domDocument->loadXML($this->createSuccessfulResponseXML());

        $this->orderServiceMock->expects($this->once())
            ->method('IPGApiAction')
            ->willReturn($domDocument);

        $result = $this->teleCash->cancelRecurringPayment(
            'order_id'
        );

        $this->assertInstanceOf(ConfirmRecurring::class, $result);
        $this->assertTrue($result->wasSuccessful());
    }

    public function testGetService()
    {
        $reflection = new ReflectionClass(TeleCash::class);
        $method = $reflection->getMethod('getService');
        $method->setAccessible(true);

        $result = $method->invoke($this->teleCash);

        $this->assertInstanceOf(OrderService::class, $result);
    }
}
