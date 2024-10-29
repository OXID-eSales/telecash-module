<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\IPG\API\Response;

use OxidSolutionCatalysts\TeleCash\IPG\API\AbstractResponse;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Class AbstractResponseTest
 */
class AbstractResponseTest extends TestCase
{
    private $abstractResponse;

    protected function setUp(): void
    {
        $this->abstractResponse = $this->createMock(AbstractResponse::class);
    }

    public function testFirstElementByTagNSString()
    {
        $doc = new \DOMDocument();
        $doc->loadXML('<root xmlns="http://example.com"><element>Test</element></root>');

        $reflection = new ReflectionClass(AbstractResponse::class);
        $method = $reflection->getMethod('firstElementByTagNSString');
        $method->setAccessible(true);

        $result = $method->invokeArgs($this->abstractResponse, [$doc, 'http://example.com', 'element']);
        $this->assertEquals('Test', $result);

        $result = $method->invokeArgs(
            $this->abstractResponse,
            [$doc, 'http://example.com', 'nonexistentelement', true, 'default']
        );
        $this->assertEquals('default', $result);
    }

    public function testFirstElementByTagNSStringThrowsException()
    {
        $this->expectException(\Exception::class);

        $doc = new \DOMDocument();
        $doc->loadXML('<root xmlns="http://example.com"></root>');

        $reflection = new ReflectionClass(AbstractResponse::class);
        $method = $reflection->getMethod('firstElementByTagNSString');
        $method->setAccessible(true);

        $method->invokeArgs($this->abstractResponse, [$doc, 'http://example.com', 'nonexistent']);
    }
}
