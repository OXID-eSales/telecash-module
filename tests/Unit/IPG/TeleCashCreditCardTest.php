<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\IPG;

use OxidSolutionCatalysts\TeleCash\Core\Service\Logger;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action\Validation;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action\Confirm;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action\Display;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Order\Sell;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashConstants;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashCreditCard;
use PHPUnit\Framework\TestCase;

class TeleCashCreditCardTest extends TestCase
{
    private TeleCashCreditCard $teleCash;
    private $orderServiceMock;
    private $loggerMock;

    protected function setUp(): void
    {
        $this->orderServiceMock = $this->createMock(OrderService::class);
        $this->loggerMock = $this->createMock(Logger::class);

        $this->teleCash = new TeleCashCreditCard(
            'https://test.com',
            'user',
            'pass',
            '/path/to/cert',
            '/path/to/key',
            'passphrase',
            '/path/to/server/cert',
            $this->loggerMock
        );

        $reflection = new \ReflectionClass($this->teleCash);
        $property = $reflection->getProperty('myService');
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
                <ns2:HostedDataID>d56feaaf-2d96-4159-8fd6-887e07fc9052</ns2:HostedDataID>
            </ns3:DataStorageItem>';
        }

        return '<?xml version="1.0" encoding="UTF-8"?>
        <SOAP-ENV:Envelope 
            xmlns:SOAP-ENV="' . TeleCashConstants::NAMESPACE_SOAP . '"
            xmlns:ns1="' . TeleCashConstants::NAMESPACE_V1 . '"
            xmlns:ns2="' . TeleCashConstants::NAMESPACE_A1 . '"
            xmlns:ns3="' . TeleCashConstants::NAMESPACE_IPGAPI . '">
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

    private function createSoapFault()
    {
        return '<SOAP-ENV:Envelope
   xmlns:SOAP-ENV = "http://schemas.xmlsoap.org/soap/envelope/"
   xmlns:xsi = "http://www.w3.org/1999/XMLSchema-instance"
   xmlns:xsd = "http://www.w3.org/1999/XMLSchema">

   <SOAP-ENV:Body>
      <SOAP-ENV:Fault>
         <faultcode xsi:type="xsd:string">SOAP-ENV:Client</faultcode>
         <faultstring xsi:type="xsd:string">Some error mesage</faultstring>
      </SOAP-ENV:Fault>
   </SOAP-ENV:Body>
</SOAP-ENV:Envelope>';
    }

    public function testValidate(): void
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

    public function testStoreHostedData(): void
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

    public function testDisplayHostedData(): void
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

    public function testValidateHostedData(): void
    {
        $domDocument = new \DOMDocument();
        $domDocument->loadXML($this->createSuccessfulResponseXML());

        $this->orderServiceMock->expects($this->once())
            ->method('IPGApiAction')
            ->willReturn($domDocument);

        $result = $this->teleCash->validateHostedData('hosted_data_id');

        $this->assertInstanceOf(Validation::class, $result);
        $this->assertTrue($result->wasSuccessful());
    }

    public function testDeleteHostedData(): void
    {
        $domDocument = new \DOMDocument();
        $domDocument->loadXML($this->createSuccessfulResponseXML());

        $this->orderServiceMock->expects($this->once())
            ->method('IPGApiAction')
            ->willReturn($domDocument);

        $result = $this->teleCash->deleteHostedData('hosted_data_id');

        $this->assertInstanceOf(Confirm::class, $result);
        $this->assertTrue($result->wasSuccessful());
    }


    public function testSellUsingHostedData(): void
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

    public function testSell(): void
    {
        $domDocument = new \DOMDocument();
        $domDocument->loadXML($this->createSuccessfulResponseXML());

        $this->orderServiceMock->expects($this->once())
            ->method('IPGApiOrder')
            ->willReturn($domDocument);

        $result = $this->teleCash->sell('4111111111111111', '12/25', 100.00);

        $this->assertInstanceOf(Sell::class, $result);
        $this->assertTrue($result->wasSuccessful());
    }

    public function testSellWithComment(): void
    {
        $domDocument = new \DOMDocument();
        $domDocument->loadXML($this->createSuccessfulResponseXML());

        $this->orderServiceMock->expects($this->once())
            ->method('IPGApiOrder')
            ->willReturn($domDocument);

        $result = $this->teleCash->sell('4111111111111111', '12/25', 100.00, 'Test Comment');

        $this->assertInstanceOf(Sell::class, $result);
        $this->assertTrue($result->wasSuccessful());
    }

    public function testSellUsingHostedDataWithComment(): void
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

    public function testSellWithoutComment(): void
    {
        $domDocument = new \DOMDocument();
        $domDocument->loadXML($this->createSuccessfulResponseXML());

        $this->orderServiceMock->expects($this->once())
            ->method('IPGApiOrder')
            ->willReturn($domDocument);

        $result = $this->teleCash->sell('cc_numer', 'cc_valid', 100.00);

        $this->assertInstanceOf(Sell::class, $result);
        $this->assertTrue($result->wasSuccessful());
    }
}
