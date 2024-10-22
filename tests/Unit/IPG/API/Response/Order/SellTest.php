<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\IPG\API\Response\Order;

use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Order\Sell;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;
use PHPUnit\Framework\TestCase;

class SellTest extends TestCase
{
    private function createSuccessfulResponseXML(): string
    {
        return '<SOAP-ENV:Envelope 
            xmlns:SOAP-ENV="' . OrderService::NAMESPACE_SOAP . '"
            xmlns:ns1="' . OrderService::NAMESPACE_N1 . '"
            xmlns:ns2="' . OrderService::NAMESPACE_N2 . '"
            xmlns:ns3="' . OrderService::NAMESPACE_N3 . '">
            <SOAP-ENV:Body>
                <ns3:IPGApiOrderResponse>
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
                </ns3:IPGApiOrderResponse>
            </SOAP-ENV:Body>
        </SOAP-ENV:Envelope>';
    }

    private function createUnsuccessfulResponseXML(): string
    {
        return '<SOAP-ENV:Envelope 
            xmlns:SOAP-ENV="' . OrderService::NAMESPACE_SOAP . '"
            xmlns:ns1="' . OrderService::NAMESPACE_N1 . '"
            xmlns:ns2="' . OrderService::NAMESPACE_N2 . '"
            xmlns:ns3="' . OrderService::NAMESPACE_N3 . '">
            <SOAP-ENV:Body>
                <ns3:IPGApiOrderResponse>
                    <ns2:Error Code="50">
                        <ns2:ErrorMessage>Declined</ns2:ErrorMessage>
                    </ns2:Error>
                    <ns3:TransactionResult>' . Sell::TRANSACTION_RESULT_DECLINED . '</ns3:TransactionResult>
                    <ns3:successfully>false</ns3:successfully>
                </ns3:IPGApiOrderResponse>
            </SOAP-ENV:Body>
        </SOAP-ENV:Envelope>';
    }

    private function createFaultyResponseXML(): string
    {
        return '<SOAP-ENV:Envelope 
            xmlns:SOAP-ENV="' . OrderService::NAMESPACE_SOAP . '"
            xmlns:ns1="' . OrderService::NAMESPACE_N1 . '"
            xmlns:ns2="' . OrderService::NAMESPACE_N2 . '"
            xmlns:ns3="' . OrderService::NAMESPACE_N3 . '">
            <SOAP-ENV:Body>
                <ns3:IPGApiOrderResponse>
                    <ns2:Error Code="50">
                        <ns2:ErrorMessage>Declined</ns2:ErrorMessage>
                    </ns2:Error>
                    <ns3:TransactionResult>' . Sell::TRANSACTION_RESULT_DECLINED . '</ns3:TransactionResult>
                    <ns3:ProcessorResponseCode>00</ns3:ProcessorResponseCode>
                    <ns3:ProcessorResponseMessage>Function performed NOT error-free</ns3:ProcessorResponseMessage>
                </ns3:IPGApiOrderResponse>
            </SOAP-ENV:Body>
        </SOAP-ENV:Envelope>';
    }

    public function testSuccessfulSell()
    {
        $xml = $this->createSuccessfulResponseXML();
        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $sell = new Sell($doc);

        $this->assertTrue($sell->wasSuccessful());
        $this->assertEquals('123456', $sell->getApprovalCode());
        $this->assertEquals('X', $sell->getAvsResponse());
        $this->assertEquals('VISA', $sell->getBrand());
        $this->assertEquals('TEST-1234', $sell->getOrderId());
        $this->assertEquals('CREDITCARD', $sell->getPaymentType());
        $this->assertEquals('OK123', $sell->getProcessorApprovalCode());
        $this->assertEquals('7890', $sell->getProcessorReceiptNumber());
        $this->assertEquals('REF123', $sell->getProcessorReferenceNumber());
        $this->assertEquals(Sell::RESPONSE_SUCCESS, $sell->getProcessorResponse());
        $this->assertEquals('00', $sell->getProcessorResponseCode());
        $this->assertEquals('123ABC', $sell->getProcessorTraceNumber());
        $this->assertEquals('MyBank', $sell->getProvider());
        $this->assertEquals('1234567890', $sell->getTDate());
        $this->assertEquals('TID001', $sell->getTerminalId());
        $this->assertEquals('2023-04-15T14:30:00', $sell->getTransactionTime());
        $this->assertEquals(Sell::TRANSACTION_RESULT_APPROVED, $sell->getTransactionResult());
    }

    public function testUnsuccessfulSell()
    {
        $xml = $this->createUnsuccessfulResponseXML();
        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $sell = new Sell($doc);

        $this->assertFalse($sell->wasSuccessful());
        $this->assertEquals(Sell::TRANSACTION_RESULT_NOT_SUCCESSFUL, $sell->getTransactionResult());
        $this->assertEquals('50', $sell->getProcessorResponseCode());
        $this->assertEquals('Declined', $sell->getProcessorResponse());
    }

    public function testCheckIfSuccessfulWithTransactionResult()
    {
        $xml = $this->createSuccessfulResponseXML();

        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $sell = new Sell($doc);

        $this->assertTrue($sell->wasSuccessful());
    }

    public function testCheckIfSuccessfulWithoutSuccessfully()
    {
        $xml = $this->createFaultyResponseXML();

        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $sell = new Sell($doc);

        $this->assertFalse($sell->wasSuccessful());
    }
}
