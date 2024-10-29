<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\IPG\API\Service;

use ReflectionClass;
use PHPUnit\Framework\TestCase;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\SoapClientCurl;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service;

class SoapClientCurlTest extends TestCase
{
    private $soapClientCurl;
    private $curlOptions;
    private $mockCurlHandle;
    private $testableClass;

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

        $this->mockCurlHandle = curl_init();
    }

    protected function tearDown(): void
    {
        if ($this->mockCurlHandle) {
            curl_close($this->mockCurlHandle);
        }
    }

    public function testDoRequestSuccess()
    {
        $this->soapClientCurl->expects($this->once())->method('curlInit')->willReturn($this->mockCurlHandle);
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
        $this->soapClientCurl->expects($this->once())->method('curlInit')->willReturn($this->mockCurlHandle);
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

    public function testP12Certificate(): void
    {
        $curlOptions = [
            'url' => 'https://test.example.com',
            'sslCert' => '/path/to/cert.p12',
            'sslKey' => '/path/to/key.pem',
            'sslKeyPasswd' => 'test123',
            'caInfo' => null
        ];

        $testClass = $this->getMockBuilder(SoapClientCurl::class)
            ->setConstructorArgs([$curlOptions, 'testuser', 'testpass'])
            ->onlyMethods([
                'curlInit',
                'curlSetopt',
                'curlExec',
                'curlErrno',
                'curlGetinfo',
                'curlError',
                'curlClose'
            ])
            ->getMock();

        // Verify P12-specific curl options are set
        $curlSetoptCalls = [];
        $testClass->expects($this->any())
            ->method('curlSetopt')
            ->willReturnCallback(function ($handle, $option, $value) use (&$curlSetoptCalls) {
                $curlSetoptCalls[] = [$option, $value];
                return true;
            });

        // Other basic mocks
        $testClass->expects($this->once())
            ->method('curlInit')
            ->willReturn($this->mockCurlHandle);

        $testClass->expects($this->once())
            ->method('curlExec')
            ->willReturn('{"success": true}');

        $testClass->expects($this->once())
            ->method('curlErrno')
            ->willReturn(CURLE_OK);

        // Execute request
        $this->invokeMethod($testClass, 'doRequest', ['<test>request</test>']);

        // Verify P12-specific options were set
        $p12OptionsFound = false;
        foreach ($curlSetoptCalls as $call) {
            if ($call[0] === CURLOPT_SSLCERTTYPE && $call[1] === 'P12') {
                $p12OptionsFound = true;
                break;
            }
        }

        $this->assertTrue($p12OptionsFound, 'P12 certificate options were not set correctly');
    }

    /**
     * Helper method to call private/protected methods
     */
    private function invokeMethod(object $object, string $methodName, array $parameters = []): mixed
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * Erstellt eine testbare Version der SoapClientCurl Klasse
     */
    private function createTestableClass(array $curlOptions = []): TestSoapClientCurl
    {
        if (!$this->testableClass) {
            $defaultOptions = [
                'url' => 'https://httpbin.org/get',
                'sslCert' => '/path/to/cert.pem',
                'sslKey' => '/path/to/key.pem',
                'sslKeyPasswd' => 'test123',
                'caInfo' => null
            ];

            $options = array_merge($defaultOptions, $curlOptions);
            $this->testableClass = new TestSoapClientCurl($options, 'testuser', 'testpass');
        }
        return $this->testableClass;
    }

    public function testCurlInit(): void
    {
        $client = $this->createTestableClass();
        $handle = $client->publicCurlInit();

        $this->assertInstanceOf(\CurlHandle::class, $handle);
        $this->assertEquals($this->mockCurlHandle, $handle);

        curl_close($handle);
    }

    public function testCurlSetopt(): void
    {
        $client = $this->createTestableClass();

        // Teste einen einfachen curl_setopt Aufruf
        $result = $client->publicCurlSetopt($this->mockCurlHandle, CURLOPT_URL, 'https://httpbin.org/get');
        $this->assertTrue($result);

        // Überprüfe, ob die Option tatsächlich gesetzt wurde
        $info = curl_getinfo($this->mockCurlHandle, CURLINFO_EFFECTIVE_URL);
        $this->assertEquals('https://httpbin.org/get', $info);
    }

    public function testCurlExec(): void
    {
        $client = $this->createTestableClass();

        // Setze grundlegende Optionen für einen Test-Request
        curl_setopt($this->mockCurlHandle, CURLOPT_URL, 'https://httpbin.org/get');
        curl_setopt($this->mockCurlHandle, CURLOPT_RETURNTRANSFER, true);

        $response = $client->publicCurlExec($this->mockCurlHandle);

        $this->assertIsString($response);
        $this->assertNotEmpty($response);
    }

    public function testCurlError(): void
    {
        $client = $this->createTestableClass();

        // Provoziere einen Fehler mit ungültiger URL
        curl_setopt($this->mockCurlHandle, CURLOPT_URL, 'invalid-url');
        $client->publicCurlExec($this->mockCurlHandle);

        $error = $client->publicCurlError($this->mockCurlHandle);
        $this->assertNotEmpty($error);
    }

    public function testCurlErrno(): void
    {
        $client = $this->createTestableClass();

        // Provoziere einen Fehler mit ungültiger URL
        curl_setopt($this->mockCurlHandle, CURLOPT_URL, 'invalid-url');
        $client->publicCurlExec($this->mockCurlHandle);

        $errno = $client->publicCurlErrno($this->mockCurlHandle);
        $this->assertNotEquals(CURLE_OK, $errno);
    }

    public function testCurlGetinfo(): void
    {
        $client = $this->createTestableClass();

        curl_setopt($this->mockCurlHandle, CURLOPT_URL, 'https://httpbin.org/get');

        $info = $client->publicCurlGetinfo($this->mockCurlHandle, CURLINFO_EFFECTIVE_URL);
        $this->assertEquals('https://httpbin.org/get', $info);
    }

    public function testCurlClose(): void
    {
        $client = $this->createTestableClass();
        $client->publicCurlClose($this->mockCurlHandle);

        $this->assertFalse(is_resource($this->mockCurlHandle));
    }
}
