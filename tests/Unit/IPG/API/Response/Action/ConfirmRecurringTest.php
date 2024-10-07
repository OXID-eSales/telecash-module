<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\IPG\API\Response\Action;

use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action\ConfirmRecurring;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;
use PHPUnit\Framework\TestCase;

class ConfirmRecurringTest extends TestCase
{
    private function createSuccessfulResponseXML(): string
    {
        return '<SOAP-ENV:Envelope 
            xmlns:SOAP-ENV="' . OrderService::NAMESPACE_SOAP . '"
            xmlns:ns1="' . OrderService::NAMESPACE_N1 . '"
            xmlns:ns2="' . OrderService::NAMESPACE_N2 . '"
            xmlns:ns3="' . OrderService::NAMESPACE_N3 . '">
            <SOAP-ENV:Body>
                <ns3:IPGApiActionResponse>
                    <ns3:successfully>true</ns3:successfully>
                    <ns3:OrderId>12345-67890</ns3:OrderId>
                </ns3:IPGApiActionResponse>
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
                <ns3:IPGApiActionResponse>
                    <ns3:successfully>false</ns3:successfully>
                    <ns2:Error>
                        <ns2:ErrorMessage>An error occurred during recurring confirmation</ns2:ErrorMessage>
                    </ns2:Error>
                </ns3:IPGApiActionResponse>
            </SOAP-ENV:Body>
        </SOAP-ENV:Envelope>';
    }

    public function testSuccessfulConfirmRecurring()
    {
        $xml = $this->createSuccessfulResponseXML();
        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $confirmRecurring = new ConfirmRecurring($doc);

        $this->assertTrue($confirmRecurring->wasSuccessful());
        $this->assertEquals('12345-67890', $confirmRecurring->getOrderId());
        $this->assertEmpty($confirmRecurring->getErrorMessage());
    }

    public function testUnsuccessfulConfirmRecurring()
    {
        $xml = $this->createUnsuccessfulResponseXML();
        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $confirmRecurring = new ConfirmRecurring($doc);

        $this->assertFalse($confirmRecurring->wasSuccessful());
        $this->assertEquals('An error occurred during recurring confirmation', $confirmRecurring->getErrorMessage());
        $this->assertEmpty($confirmRecurring->getOrderId());
    }
}
