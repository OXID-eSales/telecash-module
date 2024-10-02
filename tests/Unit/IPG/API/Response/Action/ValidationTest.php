<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\IPG\API\Response\Action;

use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action\Validation;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;
use PHPUnit\Framework\TestCase;

class ValidationTest extends TestCase
{
    private function createSuccessfulResponseXML(): string
    {
        return '<SOAP-ENV:Envelope 
            xmlns:SOAP-ENV="' . OrderService::NAMESPACE_SOAP . '"
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

    private function createFailedResponseXML(): string
    {
        return '<SOAP-ENV:Envelope 
            xmlns:SOAP-ENV="' . OrderService::NAMESPACE_SOAP . '"
            xmlns:ns3="' . OrderService::NAMESPACE_N3 . '">
            <SOAP-ENV:Body>
                <SomeOtherResponse>
                    <ns3:successfully>false</ns3:successfully>
                </SomeOtherResponse>
            </SOAP-ENV:Body>
        </SOAP-ENV:Envelope>';
    }

    public function testSuccessfulValidation()
    {
        $xml = $this->createSuccessfulResponseXML();
        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $validation = new Validation($doc);

        $this->assertTrue($validation->wasSuccessful());
        $this->assertEmpty($validation->getErrorMessage());
    }

    public function testUnsuccessfulValidation()
    {
        $xml = $this->createUnsuccessfulResponseXML();
        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $validation = new Validation($doc);

        $this->assertFalse($validation->wasSuccessful());
        $this->assertEquals('Validation failed', $validation->getErrorMessage());
    }

    public function testFailedValidation()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Validate Call failed');

        $xml = $this->createFailedResponseXML();
        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        new Validation($doc);
    }
}
