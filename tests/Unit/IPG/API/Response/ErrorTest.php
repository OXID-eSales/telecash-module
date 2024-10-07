<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\IPG\API\Response;

use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Error;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;
use PHPUnit\Framework\TestCase;

class ErrorTest extends TestCase
{
    public function testCreateFromSoapFaultWithClientError()
    {
        $xml = '<SOAP-ENV:Envelope 
            xmlns:SOAP-ENV="' . OrderService::NAMESPACE_SOAP . '"
            xmlns:ns1="' . OrderService::NAMESPACE_N1 . '"
            xmlns:ns2="' . OrderService::NAMESPACE_N2 . '"
            xmlns:ns3="' . OrderService::NAMESPACE_N3 . '">
            <SOAP-ENV:Body>
                <SOAP-ENV:Fault>
                    <faultcode>SOAP-ENV:Client</faultcode>
                    <faultstring>MerchantException: Invalid card number</faultstring>
                    <detail>
                        <ns3:IPGApiActionResponse>
                            <ns1:Error>
                                <ns1:ErrorMessage>Invalid card number</ns1:ErrorMessage>
                            </ns1:Error>
                        </ns3:IPGApiActionResponse>
                    </detail>
                </SOAP-ENV:Fault>
            </SOAP-ENV:Body>
        </SOAP-ENV:Envelope>';

        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $error = Error::createFromSoapFault($doc);

        $this->assertInstanceOf(Error::class, $error);
        $this->assertEquals(Error::ERROR_TYPE_CLIENT, $error->getErrorType());
        $this->assertEquals('MerchantException: Invalid card number', $error->getErrorMessage());
        $this->assertEquals('MerchantException', $error->getClientErrorType());
        $this->assertEquals('Invalid card number', $error->getClientErrorDetail());
    }

    public function testCreateFromSoapFaultWithServerError()
    {
        $xml = '<SOAP-ENV:Envelope 
            xmlns:SOAP-ENV="' . OrderService::NAMESPACE_SOAP . '">
            <SOAP-ENV:Body>
                <SOAP-ENV:Fault>
                    <faultcode>SOAP-ENV:Server</faultcode>
                    <faultstring>Internal server error</faultstring>
                </SOAP-ENV:Fault>
            </SOAP-ENV:Body>
        </SOAP-ENV:Envelope>';

        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $error = Error::createFromSoapFault($doc);

        $this->assertInstanceOf(Error::class, $error);
        $this->assertEquals(Error::ERROR_TYPE_SERVER, $error->getErrorType());
        $this->assertEquals('Internal server error', $error->getErrorMessage());
    }

    public function testCreateFromSoapFaultWithProcessingError()
    {
        $xml = '<SOAP-ENV:Envelope 
            xmlns:SOAP-ENV="' . OrderService::NAMESPACE_SOAP . '"
            xmlns:ns1="' . OrderService::NAMESPACE_N1 . '"
            xmlns:ns2="' . OrderService::NAMESPACE_N2 . '"
            xmlns:ns3="' . OrderService::NAMESPACE_N3 . '">
            <SOAP-ENV:Body>
                <SOAP-ENV:Fault>
                    <faultcode>SOAP-ENV:Client</faultcode>
                    <faultstring>ProcessingException: Transaction declined</faultstring>
                    <detail>
                        <ns3:IPGApiActionResponse>
                            <ns3:ErrorMessage>Transaction declined</ns3:ErrorMessage>
                        </ns3:IPGApiActionResponse>
                    </detail>
                </SOAP-ENV:Fault>
            </SOAP-ENV:Body>
        </SOAP-ENV:Envelope>';

        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $error = Error::createFromSoapFault($doc);

        $this->assertInstanceOf(Error::class, $error);
        $this->assertEquals(Error::ERROR_TYPE_CLIENT, $error->getErrorType());
        $this->assertEquals('ProcessingException: Transaction declined', $error->getErrorMessage());
        $this->assertEquals('ProcessingException', $error->getClientErrorType());
        $this->assertEquals('Transaction declined', $error->getClientErrorDetail());
    }

    public function testCreateFromSoapFaultWithNoError()
    {
        $xml = '<SOAP-ENV:Envelope 
            xmlns:SOAP-ENV="' . OrderService::NAMESPACE_SOAP . '">
            <SOAP-ENV:Body>
                <ns3:IPGApiActionResponse xmlns:ns3="' . OrderService::NAMESPACE_N3 . '">
                    <ns3:successfully>true</ns3:successfully>
                </ns3:IPGApiActionResponse>
            </SOAP-ENV:Body>
        </SOAP-ENV:Envelope>';

        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $error = Error::createFromSoapFault($doc);

        $this->assertNull($error);
    }

    public function testToString()
    {
        $xml = '<SOAP-ENV:Envelope 
            xmlns:SOAP-ENV="' . OrderService::NAMESPACE_SOAP . '"
            xmlns:ns1="' . OrderService::NAMESPACE_N1 . '"
            xmlns:ns2="' . OrderService::NAMESPACE_N2 . '"
            xmlns:ns3="' . OrderService::NAMESPACE_N3 . '">
            <SOAP-ENV:Body>
                <SOAP-ENV:Fault>
                    <faultcode>SOAP-ENV:Client</faultcode>
                    <faultstring>MerchantException: Invalid card number</faultstring>
                    <detail>
                        <ns3:IPGApiActionResponse>
                            <ns1:Error>
                                <ns1:ErrorMessage>Invalid card number</ns1:ErrorMessage>
                            </ns1:Error>
                        </ns3:IPGApiActionResponse>
                    </detail>
                </SOAP-ENV:Fault>
            </SOAP-ENV:Body>
        </SOAP-ENV:Envelope>';

        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $error = Error::createFromSoapFault($doc);

        $this->assertEquals(
            'SOAP Error: Client-Error (MerchantException: Invalid card number)',
            (string)$error
        );
    }
}
