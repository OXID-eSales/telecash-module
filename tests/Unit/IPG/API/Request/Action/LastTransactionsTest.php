<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\IPG\API\Request\Action;

use OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action\LastTransactions;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action\Validation;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class LastTransactionsTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $orderServiceProphecy;

    protected function setUp(): void
    {
        parent::setUp();

        // Create prophecy for OrderService
        $this->orderServiceProphecy = $this->prophesize(OrderService::class);
    }

    public function testWithOrderId(): void
    {
        $orderId = '123';

        $mockResponse = $this->getMockResponse();
        $lastTransactions = new LastTransactions($this->orderServiceProphecy->reveal(), 10, $orderId);

        $this->orderServiceProphecy
            ->IPGApiAction($lastTransactions)
            ->willReturn($mockResponse)
            ->shouldBeCalledOnce();

        $result = $lastTransactions->get();

        $this->assertInstanceOf(Validation::class, $result);
        $this->assertTrue($result->wasSuccessful());
    }

    public function testWithDateRange(): void
    {
        $dtFrom = '2021-01-01';
        $dtTo = '2021-01-31';

        $mockResponse = $this->getMockResponse();
        $lastTransactions = new LastTransactions($this->orderServiceProphecy->reveal(), 10, null, $dtFrom, $dtTo);

        $this->orderServiceProphecy
            ->IPGApiAction($lastTransactions)
            ->willReturn($mockResponse)
            ->shouldBeCalledOnce();

        $result = $lastTransactions->get();

        $this->assertInstanceOf(Validation::class, $result);
        $this->assertTrue($result->wasSuccessful());
    }

    private function getMockResponse(): \DOMDocument
    {
        $mockResponse = new \DOMDocument();
        $mockResponse->loadXML($this->createSuccessfulResponseXML());
        $this->document = $mockResponse;
        return $mockResponse;
    }

    private function createSuccessfulResponseXML(): string
    {
        return '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
<SOAP-ENV:Header/>
<SOAP-ENV:Body>
<ipgapi:IPGApiActionResponse 
  xmlns:a1="http://ipg-online.com/ipgapi/schemas/a1" 
  xmlns:ipgapi="http://ipg-online.com/ipgapi/schemas/ipgapi" 
  xmlns:v1="http://ipg-online.com/ipgapi/schemas/v1">
<ipgapi:successfully>true</ipgapi:successfully>
<ipgapi:OrderId>A-504a5ebf-6424-41af-bfd1-8f9eaca23378</ipgapi:OrderId>
<v1:Billing/>
<v1:Shipping/>
<a1:TransactionValues>
<v1:CreditCardTxType>
<v1:Type>sale</v1:Type>
</v1:CreditCardTxType>
<v1:CreditCardData>
<v1:CardNumber>401200...1004</v1:CardNumber>
<v1:ExpMonth>12</v1:ExpMonth>
<v1:ExpYear>24</v1:ExpYear>
<v1:Brand>VISA</v1:Brand>
</v1:CreditCardData>
<v1:Payment>
<v1:ChargeTotal>15</v1:ChargeTotal>
<v1:Currency>978</v1:Currency>
</v1:Payment>
<v1:TransactionDetails>
<v1:OrderId>A-504a5ebf-6424-41af-bfd1-8f9eaca23378</v1:OrderId>
<v1:TDate>1677686964</v1:TDate>
<v1:TransactionOrigin>ECI</v1:TransactionOrigin>
</v1:TransactionDetails>
<ipgapi:IPGApiOrderResponse> 
<ipgapi:ApprovalCode>Y:309372:4484275011:YYYM:418881</ipgapi:ApprovalCode>
<ipgapi:AVSResponse>YYY</ipgapi:AVSResponse>
<ipgapi:Brand>VISA</ipgapi:Brand>
<ipgapi:OrderId>A-504a5ebf-6424-41af-bfd1-8f9eaca23378</ipgapi:OrderId>
<ipgapi:IpgTransactionId>84484275011</ipgapi:IpgTransactionId>
<ipgapi:PayerSecurityLevel>1</ipgapi:PayerSecurityLevel>
<ipgapi:PaymentType>CREDITCARD</ipgapi:PaymentType>
<ipgapi:ProcessorApprovalCode>309372</ipgapi:ProcessorApprovalCode>
<ipgapi:ProcessorCCVResponse>M</ipgapi:ProcessorCCVResponse>
<ipgapi:ReferencedTDate>1677686964</ipgapi:ReferencedTDate> 
<ipgapi:SchemeTransactionId>234567891234560</ipgapi:SchemeTransactionId>
<ipgapi:TDate>1677686964</ipgapi:TDate>
<ipgapi:TDateFormatted>2023.03.01 17:09:24 (CET)</ipgapi:TDateFormatted>
<ipgapi:TerminalID>80000012</ipgapi:TerminalID>
</ipgapi:IPGApiOrderResponse>
<a1:TraceNumber>418881</a1:TraceNumber>
<a1:Brand>VISA</a1:Brand>
<a1:TransactionType>SALE</a1:TransactionType>
<a1:TransactionState>CAPTURED</a1:TransactionState>
<a1:UserID>1</a1:UserID>
<a1:SubmissionComponent>API</a1:SubmissionComponent>
</a1:TransactionValues>
</ipgapi:IPGApiActionResponse>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>';
    }
}
