<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\IPG\API\Service;

use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;
use OxidSolutionCatalysts\TeleCash\IPG\API\Request\ActionRequest;
use OxidSolutionCatalysts\TeleCash\IPG\API\Request\OrderRequest;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Error;
use PHPUnit\Framework\TestCase;

class OrderServiceTest extends TestCase
{
    private $orderService;
    private $curlOptions;

    protected function setUp(): void
    {
        $this->curlOptions = [
            'url' => 'https://example.com',
            'sslCert' => '/path/to/cert',
            'sslKey' => '/path/to/key',
            'sslKeyPasswd' => 'password',
            'caInfo' => '/path/to/cainfo'
        ];
        $this->orderService = $this->getMockBuilder(OrderService::class)
            ->setConstructorArgs([$this->curlOptions, 'username', 'password', false])
            ->onlyMethods(['doRequest'])
            ->getMock();
    }

    public function testIPGApiAction()
    {
        $actionRequest = $this->createMock(ActionRequest::class);
        $actionRequest->method('getDocument')->willReturn(new \DOMDocument());
        $actionRequest->method('getElement')->willReturn(new \DOMElement('dummy'));

        $actionXml = '<SOAP-ENV:Envelope
        xmlns:SOAP-ENV="' . OrderService::NAMESPACE_SOAP . '"
        xmlns:ns1="' . OrderService::NAMESPACE_N1 . '"
        xmlns:ns2="' . OrderService::NAMESPACE_N2 . '"
        xmlns:ns3="' . OrderService::NAMESPACE_N3 . '">
        <SOAP-ENV:Body>
        <ns3:IPGApiActionResponse></ns3:IPGApiActionResponse>
        </SOAP-ENV:Body>
        </SOAP-ENV:Envelope>';
        $this->orderService->expects($this->once())
            ->method('doRequest')
            ->willReturn($actionXml);

        $result = $this->orderService->IPGApiAction($actionRequest);

        $this->assertInstanceOf(\DOMDocument::class, $result);
    }

    public function testIPGApiOrder()
    {
        $orderRequest = $this->createMock(OrderRequest::class);
        $orderRequest->method('getDocument')->willReturn(new \DOMDocument());
        $orderRequest->method('getElement')->willReturn(new \DOMElement('dummy'));

        $errorXml = '<SOAP-ENV:Envelope 
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

        $this->orderService->expects($this->once())
            ->method('doRequest')
            ->willReturn($errorXml);

        $result = $this->orderService->IPGApiOrder($orderRequest);

        $this->assertInstanceOf(Error::class, $result);
        $this->assertEquals(Error::ERROR_TYPE_CLIENT, $result->getErrorType());
        $this->assertEquals('MerchantException: Invalid card number', $result->getErrorMessage());
        $this->assertEquals('MerchantException', $result->getClientErrorType());
        $this->assertEquals('Invalid card number', trim($result->getClientErrorDetail()));
    }

    public function testIPGApiActionWithError()
    {
        $actionRequest = $this->createMock(ActionRequest::class);
        $actionRequest->method('getDocument')->willReturn(new \DOMDocument());
        $actionRequest->method('getElement')->willReturn(new \DOMElement('dummy'));

        $errorXml = '<SOAP-ENV:Envelope 
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
        $this->orderService->expects($this->once())
            ->method('doRequest')
            ->willReturn($errorXml);

        $result = $this->orderService->IPGApiAction($actionRequest);

        $this->assertInstanceOf(Error::class, $result);
        $this->assertEquals(Error::ERROR_TYPE_CLIENT, $result->getErrorType());
        $this->assertEquals('MerchantException: Invalid card number', $result->getErrorMessage());
        $this->assertEquals('MerchantException', $result->getClientErrorType());
        $this->assertEquals('Invalid card number', trim($result->getClientErrorDetail()));
    }

    public function testDumpDOMElement()
    {
        $doc = new \DOMDocument();
        $element = $doc->createElement('test', 'content');
        $doc->appendChild($element);

        ob_start();
        $this->orderService->dumpDOMElement($element);
        $output = ob_get_clean();

        $this->assertStringContainsString('<test>content</test>', $output);
    }

    public function testIGPApiActionWithDebug()
    {
        $orderMock = $this->getMockBuilder(OrderService::class)
            ->setConstructorArgs([$this->curlOptions, 'username', 'password', true])
            ->onlyMethods(['doRequest'])
            ->getMock();

        $actionRequest = $this->createMock(ActionRequest::class);
        $actionRequest->method('getDocument')->willReturn(new \DOMDocument());
        $actionRequest->method('getElement')->willReturn(new \DOMElement('dummy'));

        $actionXml = '<SOAP-ENV:Envelope
        xmlns:SOAP-ENV="' . OrderService::NAMESPACE_SOAP . '"
        xmlns:ns1="' . OrderService::NAMESPACE_N1 . '"
        xmlns:ns2="' . OrderService::NAMESPACE_N2 . '"
        xmlns:ns3="' . OrderService::NAMESPACE_N3 . '">
        <SOAP-ENV:Body>
        <ns3:IPGApiActionResponse></ns3:IPGApiActionResponse>
        </SOAP-ENV:Body>
        </SOAP-ENV:Envelope>';

        $orderMock->expects($this->any())
            ->method('doRequest')
            ->willReturn($actionXml);

        ob_start();
        $result = $orderMock->IPGApiAction($actionRequest);
        ob_end_clean();

        $this->assertInstanceOf(\DOMDocument::class, $result);
    }

    public function testExceptionWithFalseFromDoRequest()
    {
        $actionRequest = $this->createMock(ActionRequest::class);
        $actionRequest->method('getDocument')->willReturn(new \DOMDocument());
        $actionRequest->method('getElement')->willReturn(new \DOMElement('dummy'));

        $this->orderService->expects($this->once())
            ->method('doRequest')
            ->willReturn(false);

        $this->expectException(\Exception::class);
        $result = $this->orderService->IPGApiAction($actionRequest);
    }

    public function testExceptionWithNullFromDoRequest()
    {
        $actionRequest = $this->createMock(ActionRequest::class);
        $actionRequest->method('getDocument')->willReturn(new \DOMDocument());
        $actionRequest->method('getElement')->willReturn(new \DOMElement('dummy'));

        $this->orderService->expects($this->once())
            ->method('doRequest')
            ->willReturn(null);

        $this->expectException(\Exception::class);
        $result = $this->orderService->IPGApiAction($actionRequest);
    }

    public function testDumpXML()
    {
        $xml = '<level1><inner>value</inner></level1>';

        ob_start();
        $this->orderService->dumpXML($xml);
        $output = ob_get_clean();

        $this->assertEquals('string(64) "<?xml version="1.0"?>
<level1>
  <inner>value</inner>
</level1>
"
', $output);
    }
}
