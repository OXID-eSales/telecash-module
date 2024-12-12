<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\IPG;

use OxidSolutionCatalysts\TeleCash\Core\Service\Logger;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\BillingData;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Order\Sell;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashConstants;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashDirectDebit;
use PHPUnit\Framework\TestCase;

class TeleCashDirectDebitTest extends TestCase
{
    private TeleCashDirectDebit $teleCash;
    private $orderServiceMock;
    private $loggerMock;
    private BillingData $billingDataMock;

    protected function setUp(): void
    {
        $this->orderServiceMock = $this->createMock(OrderService::class);
        $this->loggerMock = $this->createMock(Logger::class);
        $this->billingDataMock = new BillingData('John Doe');

        $this->teleCash = new TeleCashDirectDebit(
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

        $serviceProperty = $reflection->getProperty('myService');
        $serviceProperty->setValue($this->teleCash, $this->orderServiceMock);

        $billingProperty = $reflection->getProperty('billingData');
        $billingProperty->setValue($this->teleCash, $this->billingDataMock);
    }

    public function testSellWithBankAccountAndComment(): void
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

    public function testSellWithBankAccountNoComment(): void
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

    public function testSellWithIBAN(): void
    {
        $domDocument = new \DOMDocument();
        $domDocument->loadXML($this->createSuccessfulResponseXML());

        $this->orderServiceMock->expects($this->once())
            ->method('IPGApiOrder')
            ->willReturn($domDocument);

        $result = $this->teleCash->sellWithIBAN(
            'DE89370400440532013000',
            10.00
        );

        $this->assertInstanceOf(Sell::class, $result);
        $this->assertTrue($result->wasSuccessful());
    }

    public function testSellWithIBANAndComment(): void
    {
        $domDocument = new \DOMDocument();
        $domDocument->loadXML($this->createSuccessfulResponseXML());

        $this->orderServiceMock->expects($this->once())
            ->method('IPGApiOrder')
            ->willReturn($domDocument);

        $result = $this->teleCash->sellWithIBAN(
            'DE89370400440532013000',
            10.00,
            'Some comment'
        );

        $this->assertInstanceOf(Sell::class, $result);
        $this->assertTrue($result->wasSuccessful());
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
                    <ns3:successfully>true</ns3:successfully>
                </ns3:' . $responseType . '>
            </SOAP-ENV:Body>
        </SOAP-ENV:Envelope>';
    }
}
