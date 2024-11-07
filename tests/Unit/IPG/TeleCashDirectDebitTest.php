<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\IPG;

use OxidSolutionCatalysts\TeleCash\IPG\API\Model\BillingData;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Order\Sell;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashDirectDebit;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class TeleCashDirectDebitTest extends TestCase
{
    private $teleCash;
    private $orderServiceMock;
    private $billingDataMock;

    public function testSellWithBankAccountAndComment()
    {
        $domDocument = new \DOMDocument();
        $domDocument->loadXML($this->createSuccessfulResponseXML());

        $this->orderServiceMock->expects($this->once())
            ->method('IPGApiOrder')
            ->willReturn($domDocument);

        $result = $this->teleCash->sellWithBankAccount(
            '123456789',
            '987654321',
            10.00,
            'Some comment'
        );

        $this->assertInstanceOf(Sell::class, $result);
        $this->assertTrue($result->wasSuccessful());
    }

    public function testSellWithBankAccountNoComment()
    {
        $domDocument = new \DOMDocument();
        $domDocument->loadXML($this->createSuccessfulResponseXML());

        $this->orderServiceMock->expects($this->once())
            ->method('IPGApiOrder')
            ->willReturn($domDocument);

        $result = $this->teleCash->sellWithBankAccount(
            '123456789',
            '987654321',
            10.00
        );

        $this->assertInstanceOf(Sell::class, $result);
        $this->assertTrue($result->wasSuccessful());
    }

    public function testSellWithIBANAndComment()
    {
        $domDocument = new \DOMDocument();
        $domDocument->loadXML($this->createSuccessfulResponseXML());

        $this->orderServiceMock->expects($this->once())
            ->method('IPGApiOrder')
            ->willReturn($domDocument);

        $result = $this->teleCash->sellWithIBAN(
            '123456789',
            10.00
        );

        $this->assertInstanceOf(Sell::class, $result);
        $this->assertTrue($result->wasSuccessful());
    }

    public function testSellWithIBANNoComment()
    {
        $domDocument = new \DOMDocument();
        $domDocument->loadXML($this->createSuccessfulResponseXML());

        $this->orderServiceMock->expects($this->once())
            ->method('IPGApiOrder')
            ->willReturn($domDocument);

        $result = $this->teleCash->sellWithIBAN(
            '123456789',
            10.00,
            'Some comment'
        );

        $this->assertInstanceOf(Sell::class, $result);
        $this->assertTrue($result->wasSuccessful());
    }


    protected function setUp(): void
    {
        $this->orderServiceMock = $this->createMock(OrderService::class);

        $this->billingDataMock = new BillingData('John Doe');

        $this->teleCash = new TeleCashDirectDebit(
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

        $property = $reflection->getProperty('billingData');
        $property->setAccessible(true);
        $property->setValue($this->teleCash, $this->billingDataMock);
    }

    private function createSuccessfulResponseXML(
        string $responseType = 'IPGApiActionResponse'
    ): string {
        $xml = '<SOAP-ENV:Envelope 
            xmlns:SOAP-ENV="' . OrderService::NAMESPACE_SOAP . '"
            xmlns:ns1="' . OrderService::NAMESPACE_N1 . '"
            xmlns:ns2="' . OrderService::NAMESPACE_N2 . '"
            xmlns:ns3="' . OrderService::NAMESPACE_N3 . '">
            <SOAP-ENV:Body>
                <ns3:' . $responseType . '>
                    <ns3:ApprovalCode>Y:000000:4671984046:PPXM:5990310136</ns3:ApprovalCode>
                    <ns3:AVSResponse>PPX</ns3:AVSResponse>
                    <ns3:CommercialServiceProvider>TELECASH</ns3:CommercialServiceProvider>
                    <ns3:OrderId>A-9248df27-6db5-4ad8-ad99-8c1e02d6fb3f</ns3:OrderId>
                    <ns3:IpgTransactionId>84671984046</ns3:IpgTransactionId>
                    <ns3:PaymentType>DEBITDE</ns3:PaymentType>
                    <ns3:ProcessorApprovalCode>000000</ns3:ProcessorApprovalCode>
                    <ns3:ProcessorReceiptNumber>0136</ns3:ProcessorReceiptNumber>
                    <ns3:ProcessorCCVResponse>M</ns3:ProcessorCCVResponse>
                    <ns3:ProcessorReferenceNumber>000000</ns3:ProcessorReferenceNumber>
                    <ns3:ProcessorResponseCode>00</ns3:ProcessorResponseCode>
                    <ns3:ProcessorResponseMessage>' . Sell::RESPONSE_SUCCESS . '</ns3:ProcessorResponseMessage>
                    <ns3:ProcessorTraceNumber>599031</ns3:ProcessorTraceNumber>
                    <ns3:TDate>1730213686</ns3:TDate>
                    <ns3:TDateFormatted>2024.10.29 15:54:46 (CET)</ns3:TDateFormatted>
                    <ns3:TerminalID>54080602</ns3:TerminalID>
                    <ns3:TransactionResult>' . Sell::TRANSACTION_RESULT_APPROVED . '</ns3:TransactionResult>
                    <ns3:TransactionTime>1730213686</ns3:TransactionTime>
                </ns3:' . $responseType . '>
            </SOAP-ENV:Body>
        </SOAP-ENV:Envelope>';

        return $xml;
    }

    private function createUnsuccessfulResponseXML(): string
    {
        return '<SOAP-ENV:Envelope 
            xmlns:SOAP-ENV="' . OrderService::NAMESPACE_SOAP . '"
            xmlns:ns1="' . OrderService::NAMESPACE_N1 . '"
            xmlns:ns2="' . OrderService::NAMESPACE_N2 . '"
            xmlns:ns3="' . OrderService::NAMESPACE_N3 . '">
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
}
