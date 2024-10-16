<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\IPG\API\Service;

use ReflectionClass;
use PHPUnit\Framework\TestCase;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\SoapClientCurl;

class SoapClientCurlTest extends TestCase
{
    private $soapClientCurl;
    private $curlOptions;

    protected function setUp(): void
    {
        $this->curlOptions = [
            'url' => 'https://example.com/soap',
            'sslCert' => '/path/to/cert.pem',
            'sslKey' => '/path/to/key.pem',
            'sslKeyPasswd' => 'password',
            'caInfo' => '/path/to/ca.pem'
        ];

        $this->soapClientCurl = $this->getMockBuilder(SoapClientCurl::class)
            ->setConstructorArgs([$this->curlOptions, 'username', 'password'])
            ->onlyMethods(['curlInit', 'curlSetopt', 'curlExec', 'curlErrno', 'curlGetinfo', 'curlError', 'curlClose'])
            ->getMock();
    }

    public function testDoRequestSuccess()
    {
        $curlHandle = curl_init();  // Erstellen Sie ein echtes cURL-Handle für den Test

        $this->soapClientCurl->expects($this->once())->method('curlInit')->willReturn($curlHandle);
        $this->soapClientCurl->expects($this->any())->method('curlSetopt')->willReturn(true);
        $this->soapClientCurl->expects($this->once())->method('curlExec')->willReturn('Successful response');
        $this->soapClientCurl->expects($this->once())->method('curlErrno')->willReturn(CURLE_OK);
        $this->soapClientCurl->expects($this->once())->method('curlGetinfo')->willReturn(200);
        $this->soapClientCurl->expects($this->once())->method('curlError')->willReturn('');
        $this->soapClientCurl->expects($this->once())->method('curlClose');

        $reflection = new ReflectionClass(SoapClientCurl::class);
        $method = $reflection->getMethod('doRequest');
        $method->setAccessible(true);

        $response = $method->invoke($this->soapClientCurl, '<?xml version="1.0"?><soap:Envelope></soap:Envelope>');
        $this->assertEquals('Successful response', $response);
    }

    public function testDoRequestError()
    {
        $curlHandle = curl_init();

        $this->soapClientCurl->expects($this->once())->method('curlInit')->willReturn($curlHandle);
        $this->soapClientCurl->expects($this->any())->method('curlSetopt')->willReturn(true);
        $this->soapClientCurl->expects($this->once())->method('curlExec')->willReturn(false);
        $this->soapClientCurl->expects($this->once())->method('curlErrno')->willReturn(CURLE_COULDNT_CONNECT);
        $this->soapClientCurl->expects($this->never())->method('curlGetinfo');
        $this->soapClientCurl->expects($this->once())->method('curlError')->willReturn('Could not connect');
        $this->soapClientCurl->expects($this->once())->method('curlClose');

        $reflection = new ReflectionClass(SoapClientCurl::class);
        $method = $reflection->getMethod('doRequest');
        $method->setAccessible(true);

        $response = $method->invoke($this->soapClientCurl, '<?xml version="1.0"?><soap:Envelope></soap:Envelope>');
        $this->assertFalse($response);

        $this->assertEquals('0: Could not connect', $this->soapClientCurl->getErrorMessage());
    }
}
