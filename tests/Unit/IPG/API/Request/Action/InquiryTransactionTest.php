<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\IPG\API\Request\Action;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action\InquiryTransaction;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action\Validation;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Error;

class InquiryTransactionTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $orderServiceProphecy;
    private InquiryTransaction $inquiryTransaction;
    private \DOMDocument $document;

    protected function setUp(): void
    {
        parent::setUp();

        // Create prophecy for OrderService
        $this->orderServiceProphecy = $this->prophesize(OrderService::class);

        // Create actual instance of InquiryTransaction with the prophesized service
        $this->inquiryTransaction = new InquiryTransaction($this->orderServiceProphecy->reveal());

        // Store document reference for assertions
        $xmlSource = $this->createSuccessfulResponseXML();
        $this->document = new \DOMDocument();
        $this->document->loadXML($xmlSource);
    }


    public function testGetByIPGTransactionIdSuccess(): void
    {
        // Arrange
        $storeId = '123456789';
        $mockResponse = $this->getMockResponse();

        $this->orderServiceProphecy
            ->IPGApiAction($this->inquiryTransaction)
            ->willReturn($mockResponse)
            ->shouldBeCalledOnce();

        // Act
        $result = $this->inquiryTransaction->getByIPGTransactionId($storeId);

        // Assert
        $this->assertInstanceOf(Validation::class, $result);

        // Verify the DOM structure
        $elements = $this->document->getElementsByTagNameNS(OrderService::NAMESPACE_N3, 'IpgTransactionId');
        $this->assertEquals(1, $elements->length);
        $this->assertEquals($storeId, $elements->item(0)->nodeValue);

        // Verify prophecy
        $this->orderServiceProphecy->checkProphecyMethodsPredictions();
    }

    public function testGetByIPGTransactionIdError(): void
    {
        // Arrange
        $storeId = '123456789';
        $mockError = $this->prophesize(Error::class)->reveal();

        $this->orderServiceProphecy
            ->IPGApiAction($this->inquiryTransaction)
            ->willReturn($mockError)
            ->shouldBeCalledOnce();

        // Act
        $result = $this->inquiryTransaction->getByIPGTransactionId($storeId);

        // Assert
        $this->assertInstanceOf(Error::class, $result);

        // Verify prophecy
        $this->orderServiceProphecy->checkProphecyMethodsPredictions();
    }

    public function testGetByOrderIdAndTDateSuccess(): void
    {
        // Arrange
        $orderId = 'ORDER123';
        $tDate = '20240422';
        $mockResponse = $this->getMockResponse();

        $this->orderServiceProphecy
            ->IPGApiAction($this->inquiryTransaction)
            ->willReturn($mockResponse)
            ->shouldBeCalledOnce();

        // Act
        $result = $this->inquiryTransaction->getByOrderIdAndTDate($orderId, $tDate);
        // Assert
        $this->assertInstanceOf(Validation::class, $result);

        // Verify the DOM structure
        $orderIdElements = $this->document->getElementsByTagNameNS(
            OrderService::NAMESPACE_N1,
            'OrderId'
        );
        $this->assertEquals(1, $orderIdElements->length);
        $this->assertEquals($orderId, $orderIdElements->item(0)->nodeValue);

        $tDateElements = $this->document->getElementsByTagNameNS(
            OrderService::NAMESPACE_N1,
            'TDate'
        );
        $this->assertEquals(1, $tDateElements->length);
        $this->assertEquals($tDate, $tDateElements->item(0)->nodeValue);

        // Verify prophecy
        $this->orderServiceProphecy->checkProphecyMethodsPredictions();
    }

    public function testGetByOrderIdAndTDateError(): void
    {
        // Arrange
        $orderId = 'ORDER123';
        $tDate = '20240422';
        $mockError = $this->prophesize(Error::class)->reveal();

        $this->orderServiceProphecy
            ->IPGApiAction($this->inquiryTransaction)
            ->willReturn($mockError)
            ->shouldBeCalledOnce();

        // Act
        $result = $this->inquiryTransaction->getByOrderIdAndTDate($orderId, $tDate);

        // Assert
        $this->assertInstanceOf(Error::class, $result);

        // Verify prophecy
        $this->orderServiceProphecy->checkProphecyMethodsPredictions();
    }

    private function getMockResponse()
    {
        $mockResponse = new \DOMDocument();
        $mockResponse->loadXML($this->createSuccessfulResponseXML());
        $this->document = $mockResponse;
        return $mockResponse;
    }

    private function getMockError()
    {
        $mockError = new \DOMDocument();
        $mockError->loadXML($this->createUnsuccessfulResponseXML());
        $this->document = $mockError;
        return $mockError;
    }

    private function getMockFailed()
    {
        $mockFailed = new \DOMDocument();
        $mockFailed->loadXML($this->createFailedResponseXML());
        $this->document = $mockFailed;
        return $mockFailed;
    }

    private function createSuccessfulResponseXML(): string
    {
        // copied from documentation
        return '<SOAP-ENV:Envelope 
            xmlns:SOAP-ENV="' . OrderService::NAMESPACE_SOAP . '">
<SOAP-ENV:Header/>
<SOAP-ENV:Body>
    <ns3:IPGApiActionResponse
            xmlns:ns1="' . OrderService::NAMESPACE_N1 . '"
            xmlns:ns2="' . OrderService::NAMESPACE_N2 . '"
            xmlns:ns3="' . OrderService::NAMESPACE_N3 . '">
        <ns3:successfully>true</ns3:successfully>
        <ns3:OrderId>ORDER123</ns3:OrderId>
        <ns1:Billing/>
        <ns1:Shipping/>
        <ns2:TransactionValues>
            <ns1:CreditCardTxType>
                <ns1:Type>sale</ns1:Type>
            </ns1:CreditCardTxType>
                <ns1:CreditCardData>
                <ns1:CardNumber>401200...1004</ns1:CardNumber>
                <ns1:ExpMonth>12</ns1:ExpMonth>
                <ns1:ExpYear>24</ns1:ExpYear>
                <ns1:Brand>VISA</ns1:Brand>
            </ns1:CreditCardData>
            <ns1:Payment>
                <ns1:ChargeTotal>15</ns1:ChargeTotal>
                <ns1:Currency>978</ns1:Currency>
            </ns1:Payment>
            <ns1:TransactionDetails>
                <ns1:OrderId>ORDER123</ns1:OrderId>
                <ns1:TDate>20240422</ns1:TDate>
                <ns1:TransactionOrigin>ECI</ns1:TransactionOrigin>
            </ns1:TransactionDetails>
            <ns3:IPGApiOrderResponse> 
                <ns3:ApprovalCode>Y:309372:4484275011:YYYM:418881</ns3:ApprovalCode>
                <ns3:AVSResponse>YYY</ns3:AVSResponse>
                <ns3:Brand>VISA</ns3:Brand>
                <ns3:OrderId>ORDER123</ns3:OrderId>
                <ns3:IpgTransactionId>123456789</ns3:IpgTransactionId>
                <ns3:PayerSecurityLevel>1</ns3:PayerSecurityLevel>
                <ns3:PaymentType>CREDITCARD</ns3:PaymentType>
                <ns3:ProcessorApprovalCode>309372</ns3:ProcessorApprovalCode>
                <ns3:ProcessorCCVResponse>M</ns3:ProcessorCCVResponse>
                <ns3:ReferencedTDate>1677686964</ns3:ReferencedTDate> 
                <ns3:SchemeTransactionId>234567891234560</ns3:SchemeTransactionId>
                <ns3:TDate>20240422</ns3:TDate>
                <ns3:TDateFormatted>2024.04.22 17:09:24 (CET)</ns3:TDateFormatted>
                <ns3:TerminalID>80000012</ns3:TerminalID>
            </ns3:IPGApiOrderResponse>
            <ns2:TraceNumber>418881</ns2:TraceNumber>
            <ns2:Brand>VISA</ns2:Brand>
            <ns2:TransactionType>SALE</ns2:TransactionType>
            <ns2:TransactionState>CAPTURED</ns2:TransactionState>
            <ns2:UserID>1</ns2:UserID>
            <ns2:SubmissionComponent>API</ns2:SubmissionComponent>
        </ns2:TransactionValues>
    </ns3:IPGApiActionResponse>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>';
    }

    private function createUnsuccessfulResponseXML(): string
    {
        return '<SOAP-ENV:Envelope 
            xmlns:SOAP-ENV="' . OrderService::NAMESPACE_SOAP . '"
            xmlns:ns2="' . OrderService::NAMESPACE_N2 . '"
            xmlns:ns3="' . OrderService::NAMESPACE_N3 . '">            
            <SOAP-ENV:Body>
                <ns3:IPGApiActionResponse>
                    <ns3:successfully>false</ns3:successfully>
                    <ns2:Error>
                        <ns2:ErrorMessage>Validation failed</ns2:ErrorMessage>
                    </ns2:Error>
                </ns3:IPGApiActionResponse>
            </SOAP-ENV:Body>
        </SOAP-ENV:Envelope>';
    }

    private function createFailedResponseXML()
    {
        return '<SOAP-ENV:Envelope 
            xmlns:SOAP-ENV="' . OrderService::NAMESPACE_SOAP . '"
            xmlns:ns2="' . OrderService::NAMESPACE_N2 . '"
            xmlns:ns3="' . OrderService::NAMESPACE_N3 . '">            
            <SOAP-ENV:Body>
                <ns3:IPGApiActionResponse>
                    <ns3:successfully>false</ns3:successfully>
                    <ns2:Error>
                        <ns2:ErrorMessage>Validation failed</ns2:ErrorMessage>
                    </ns2:Error>
                </ns3:IPGApiActionResponse>
            </SOAP-ENV:Body>
        </SOAP-ENV:Envelope>';
    }
}
