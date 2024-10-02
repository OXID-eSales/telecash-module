<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\IPG\API\Response\Action;

use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action\Confirm;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;
use PHPUnit\Framework\TestCase;

class ConfirmTest extends TestCase
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
                        <ns2:ErrorMessage>An error occurred</ns2:ErrorMessage>
                    </ns2:Error>
                </ns3:IPGApiActionResponse>
            </SOAP-ENV:Body>
        </SOAP-ENV:Envelope>';
    }

    private function createFailedResponseXML(): string
    {
        return '<SOAP-ENV:Envelope 
            xmlns:SOAP-ENV="' . OrderService::NAMESPACE_SOAP . '"
            xmlns:ns1="' . OrderService::NAMESPACE_N1 . '"
            xmlns:ns2="' . OrderService::NAMESPACE_N2 . '"
            xmlns:ns3="' . OrderService::NAMESPACE_N3 . '">
            <SOAP-ENV:Body>
                <ns3:IPGApiActionResponse>
                    <ns3:successfully>false</ns3:successfully>
                </ns3:IPGApiActionResponse>
            </SOAP-ENV:Body>
        </SOAP-ENV:Envelope>';
    }

    public function testSuccessfulConfirm()
    {
        $xml = $this->createSuccessfulResponseXML();
        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $confirm = new Confirm($doc);

        $this->assertTrue($confirm->wasSuccessful());
        $this->assertEmpty($confirm->getErrorMessage());
    }

    public function testUnsuccessfulConfirm()
    {
        $xml = $this->createUnsuccessfulResponseXML();
        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $confirm = new Confirm($doc);

        $this->assertFalse($confirm->wasSuccessful());
        $this->assertEquals('An error occurred', $confirm->getErrorMessage());
    }

    public function testFailedConfirm()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Call failed');

        $xml = $this->createFailedResponseXML();
        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        new Confirm($doc);
    }
}
