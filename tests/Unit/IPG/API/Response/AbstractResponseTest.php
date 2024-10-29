<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\IPG\API\Response;

use OxidSolutionCatalysts\TeleCash\IPG\API\AbstractResponse;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Test class for the AbstractResponse base class
 *
 * This test suite verifies the core functionality of the AbstractResponse class,
 * particularly focusing on XML namespace handling and element extraction methods.
 */
class AbstractResponseTest extends TestCase
{
    /**
     * @var AbstractResponse Mock object for testing protected methods
     */
    private $abstractResponse;

    /**
     * Set up test environment
     *
     * Creates a mock of AbstractResponse to allow testing of protected methods
     * without needing to implement all abstract methods
     */
    protected function setUp(): void
    {
        $this->abstractResponse = $this->createMock(AbstractResponse::class);
    }

    /**
     * Tests successful extraction of element content using namespace
     *
     * Verifies that the firstElementByTagNSString method correctly:
     * 1. Handles XML namespaces
     * 2. Finds the specified element
     * 3. Returns the element's content
     */
    public function testFirstElementByTagNSString(): void
    {
        // Create a test XML document with a namespace
        $doc = new \DOMDocument();
        $doc->loadXML('<root xmlns="http://example.com"><element>Test</element></root>');

        // Use reflection to access protected method
        $reflection = new ReflectionClass(AbstractResponse::class);
        $method = $reflection->getMethod('firstElementByTagNSString');

        $result = $method->invokeArgs($this->abstractResponse, [$doc, 'http://example.com', 'element']);
        $this->assertEquals('Test', $result);

        $result = $method->invokeArgs(
            $this->abstractResponse,
            [$doc, 'http://example.com', 'nonexistentelement', true, 'default']
        );
        $this->assertEquals('default', $result);
    }

    /**
     * Tests exception handling for non-existent elements
     *
     * Verifies that the firstElementByTagNSString method:
     * 1. Properly handles missing elements
     * 2. Throws an exception when element is not found
     * 3. Maintains XML namespace awareness during search
     */
    public function testFirstElementByTagNSStringThrowsException(): void
    {
        // Expect an exception for non-existent element
        $this->expectException(\Exception::class);

        // Create a test XML document without the target element
        $doc = new \DOMDocument();
        $doc->loadXML('<root xmlns="http://example.com"></root>');

        // Use reflection to access protected method
        $reflection = new ReflectionClass(AbstractResponse::class);
        $method = $reflection->getMethod('firstElementByTagNSString');

        // Attempt to find non-existent element (should throw exception)
        $method->invokeArgs(
            $this->abstractResponse,
            [
                $doc,                   // XML document
                'http://example.com',   // Namespace URI
                'nonexistent'           // Non-existent element name
            ]
        );
    }
}
