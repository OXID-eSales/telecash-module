<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\IPG\API\Response\Action;

use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action\Display;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;
use PHPUnit\Framework\TestCase;

class DisplayTest extends TestCase
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
                    <ns2:CreditCardData>
                        <ns1:CardNumber>4111111111111111</ns1:CardNumber>
                        <ns1:ExpMonth>12</ns1:ExpMonth>
                        <ns1:ExpYear>2025</ns1:ExpYear>
                        <ns2:HostedDataID>HDI12345</ns2:HostedDataID>
                    </ns2:CreditCardData>
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
                    <ns3:successfully>true</ns3:successfully>
                    <ns2:Error>
                        <ns2:ErrorMessage>An error occurred during display</ns2:ErrorMessage>
                    </ns2:Error>
                </ns3:IPGApiActionResponse>
            </SOAP-ENV:Body>
        </SOAP-ENV:Envelope>';
    }

    private function createFailedResponseXML(): string
    {
        return '<SOAP-ENV:Envelope 
            xmlns:SOAP-ENV="' . OrderService::NAMESPACE_SOAP . '"
            xmlns:ns3="' . OrderService::NAMESPACE_N3 . '">
            <SOAP-ENV:Body>
                <ns3:IPGApiActionResponse>
                    <ns3:successfully>false</ns3:successfully>
                </ns3:IPGApiActionResponse>
            </SOAP-ENV:Body>
        </SOAP-ENV:Envelope>';
    }

    public function testSuccessfulDisplay()
    {
        $xml = $this->createSuccessfulResponseXML();
        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $display = new Display($doc);

        $this->assertTrue($display->wasSuccessful());
        $this->assertEquals('4111111111111111', $display->getCCNumber());
        $this->assertEquals('12/2025', $display->getCCValid());
        $this->assertEquals('HDI12345', $display->getHostedDataId());
        $this->assertEmpty($display->getErrorMessage());
    }

    public function testUnsuccessfulDisplay()
    {
        $xml = $this->createUnsuccessfulResponseXML();
        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $display = new Display($doc);

        $this->assertFalse($display->wasSuccessful());
        $this->assertEquals('An error occurred during display', $display->getErrorMessage());
        $this->assertEmpty($display->getCCNumber());
        $this->assertEmpty($display->getCCValid());
        $this->assertEmpty($display->getHostedDataId());
    }

    public function testFailedDisplay()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Display Call failed');

        $xml = $this->createFailedResponseXML();
        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        new Display($doc);
    }
}
