<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\IPG\API\Service;

use OxidSolutionCatalysts\TeleCash\Core\Service\Logger;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;
use OxidSolutionCatalysts\TeleCash\IPG\API\Request\ActionRequest;
use OxidSolutionCatalysts\TeleCash\IPG\API\Request\OrderRequest;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Error;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashConstants;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use ValueError;

class OrderServiceTest extends TestCase
{
    private $orderService;
    private $curlOptions;
    private Logger $logger;

    protected function setUp(): void
    {
        // Create mock for Logger
        $this->logger = $this->createMock(Logger::class);

        $this->curlOptions = [
            'url' => 'https://example.com',
            'sslCert' => '/path/to/cert',
            'sslKey' => '/path/to/key',
            'sslKeyPasswd' => 'password',
            'caInfo' => '/path/to/cainfo'
        ];

        // Create partial mock for OrderService
        $this->orderService = $this->getMockBuilder(OrderService::class)
            ->setConstructorArgs([
                $this->curlOptions,
                'username',
                'password',
                $this->logger
            ])
            ->onlyMethods(['doRequest'])
            ->getMock();
    }

    public function testDumpDOMElement(): void
    {
        $doc = new \DOMDocument();
        $element = $doc->createElement('test', 'content');
        $doc->appendChild($element);

        // Configure logger mock to expect the log call
        $this->logger->expects($this->once())
            ->method('log')
            ->with(
                'debug',
                $this->stringContains('<test>content</test>')
            );

        $this->orderService->dumpDOMElement('TestType', $element);
    }

    public function testIPGApiAction(): void
    {
        // Create mocks
        $actionRequest = $this->createMock(ActionRequest::class);
        $doc = new \DOMDocument();
        $element = $doc->createElement('dummy');
        $doc->appendChild($element);

        $actionRequest->method('getDocument')->willReturn($doc);
        $actionRequest->method('getElement')->willReturn($element);

        // Configure logger expectations for debug output
        $this->logger->expects($this->exactly(2))
            ->method('log')
            ->with(
                'debug',
                $this->stringContains('Debug:')
            );

        $actionXml = '<SOAP-ENV:Envelope
            xmlns:SOAP-ENV="' . TeleCashConstants::NAMESPACE_SOAP . '"
            xmlns:ns1="' . TeleCashConstants::NAMESPACE_V1 . '"
            xmlns:ns2="' . TeleCashConstants::NAMESPACE_A1 . '"
            xmlns:ns3="' . TeleCashConstants::NAMESPACE_IPGAPI . '">
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

    public function testIPGApiActionWithError(): void
    {
        $actionRequest = $this->createMock(ActionRequest::class);
        $doc = new \DOMDocument();
        $element = $doc->createElement('dummy');
        $doc->appendChild($element);

        $actionRequest->method('getDocument')->willReturn($doc);
        $actionRequest->method('getElement')->willReturn($element);

        $this->logger->expects($this->exactly(2))
            ->method('log')
            ->with('debug', $this->stringContains('Debug:'));

        $errorXml = '<?xml version="1.0" encoding="UTF-8"?>
        <SOAP-ENV:Envelope 
            xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
            xmlns:ipgapi="http://ipg-online.com/ipgapi/schemas/ipgapi"
            xmlns:v1="http://ipg-online.com/ipgapi/schemas/v1">
            <SOAP-ENV:Body>
                <SOAP-ENV:Fault>
                    <faultcode>SOAP-ENV:Client</faultcode>
                    <faultstring>ProcessingException: Transaction failed</faultstring>
                    <detail>
                        <ipgapi:IPGApiActionResponse>
                            <ipgapi:successfully>false</ipgapi:successfully>
                            <ipgapi:OrderId>A-123456789</ipgapi:OrderId>
                            <ipgapi:ErrorElement>
                                <ipgapi:ErrorCode>500.1</ipgapi:ErrorCode>
                                <ipgapi:ErrorMessage>ProcessingException: Transaction failed</ipgapi:ErrorMessage>
                                <ipgapi:ErrorDetail>ProcessingException</ipgapi:ErrorDetail>
                            </ipgapi:ErrorElement>
                        </ipgapi:IPGApiActionResponse>
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
        $this->assertEquals('ProcessingException: Transaction failed', $result->getErrorMessage());
        $this->assertEquals('ProcessingException', $result->getClientErrorType());
        $this->assertEquals('ProcessingException: Transaction failed', $result->getErrorMessage());
    }

    public function testHandleDebug(): void
    {
        $testXml = '<?xml version="1.0"?><test><node>value</node></test>';

        $this->logger->expects($this->once())
            ->method('log')
            ->with(
                'debug',
                $this->stringContains('Debug: TestType:')
            );

        $this->orderService->handleDebug('TestType', $testXml);
    }

    public function testIPGApiActionWithEmptyResponse(): void
    {
        $actionRequest = $this->createMock(ActionRequest::class);
        $doc = new \DOMDocument();
        $element = $doc->createElement('dummy');
        $doc->appendChild($element);

        $actionRequest->method('getDocument')->willReturn($doc);
        $actionRequest->method('getElement')->willReturn($element);

        $this->logger->expects($this->once())
            ->method('log')
            ->with('debug', $this->stringContains('Debug:'));

        $this->orderService->expects($this->once())
            ->method('doRequest')
            ->willReturn('');

        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('DOMDocument::loadXML(): Argument #1 ($source) must not be empty');

        $this->orderService->IPGApiAction($actionRequest);
    }

    public function testIPGApiActionWithFalseResponse(): void
    {
        $actionRequest = $this->createMock(ActionRequest::class);
        $doc = new \DOMDocument();
        $element = $doc->createElement('dummy');
        $doc->appendChild($element);

        $actionRequest->method('getDocument')->willReturn($doc);
        $actionRequest->method('getElement')->willReturn($element);

        $this->logger->expects($this->once())
            ->method('log')
            ->with('debug', $this->stringContains('Debug:'));

        $this->orderService->expects($this->once())
            ->method('doRequest')
            ->willThrowException(new \RuntimeException('Empty API response'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Empty API response');

        $this->orderService->IPGApiAction($actionRequest);
    }

    public function testIPGApiOrderWithServerError(): void
    {
        $orderRequest = $this->createMock(OrderRequest::class);
        $doc = new \DOMDocument();
        $element = $doc->createElement('dummy');
        $doc->appendChild($element);

        $orderRequest->method('getDocument')->willReturn($doc);
        $orderRequest->method('getElement')->willReturn($element);

        $this->logger->expects($this->exactly(2))
            ->method('log')
            ->with('debug', $this->stringContains('Debug:'));

        $errorXml = '<?xml version="1.0" encoding="UTF-8"?>
        <SOAP-ENV:Envelope 
            xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
            xmlns:ipgapi="http://ipg-online.com/ipgapi/schemas/ipgapi"
            xmlns:v1="http://ipg-online.com/ipgapi/schemas/v1">
            <SOAP-ENV:Body>
                <SOAP-ENV:Fault>
                    <faultcode>SOAP-ENV:Server</faultcode>
                    <faultstring>Internal server error</faultstring>
                    <detail>
                        <ipgapi:IPGApiOrderResponse>
                            <v1:Error>
                                <v1:ErrorMessage>Server processing error</v1:ErrorMessage>
                            </v1:Error>
                        </ipgapi:IPGApiOrderResponse>
                    </detail>
                </SOAP-ENV:Fault>
            </SOAP-ENV:Body>
        </SOAP-ENV:Envelope>';

        $this->orderService->expects($this->once())
            ->method('doRequest')
            ->willReturn($errorXml);

        $result = $this->orderService->IPGApiOrder($orderRequest);

        $this->assertInstanceOf(Error::class, $result);
        $this->assertEquals(Error::ERROR_TYPE_SERVER, $result->getErrorType());
        $this->assertEquals('Internal server error', $result->getErrorMessage());
    }

    public function testIPGApiOrderWithSuccessResponse(): void
    {
        $orderRequest = $this->createMock(OrderRequest::class);
        $doc = new \DOMDocument();
        $element = $doc->createElement('dummy');
        $doc->appendChild($element);

        $orderRequest->method('getDocument')->willReturn($doc);
        $orderRequest->method('getElement')->willReturn($element);

        $this->logger->expects($this->exactly(2))
            ->method('log')
            ->with('debug', $this->stringContains('Debug:'));

        $successXml = '<?xml version="1.0" encoding="UTF-8"?>
        <SOAP-ENV:Envelope 
            xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
            xmlns:ipgapi="http://ipg-online.com/ipgapi/schemas/ipgapi"
            xmlns:v1="http://ipg-online.com/ipgapi/schemas/v1">
            <SOAP-ENV:Body>
                <ipgapi:IPGApiOrderResponse>
                    <v1:TransactionResult>APPROVED</v1:TransactionResult>
                </ipgapi:IPGApiOrderResponse>
            </SOAP-ENV:Body>
        </SOAP-ENV:Envelope>';

        $this->orderService->expects($this->once())
            ->method('doRequest')
            ->willReturn($successXml);

        $result = $this->orderService->IPGApiOrder($orderRequest);

        $this->assertInstanceOf(\DOMDocument::class, $result);
        $this->assertStringContainsString('APPROVED', $result->saveXML());
    }
}
