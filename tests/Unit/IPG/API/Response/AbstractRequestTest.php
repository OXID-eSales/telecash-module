<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\IPG\API;

use OxidSolutionCatalysts\TeleCash\IPG\API\AbstractRequest;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class AbstractRequestTest extends TestCase
{
    private $abstractRequest;

    protected function setUp(): void
    {
        $this->abstractRequest = new class extends AbstractRequest {
            public function __construct()
            {
                $this->document = new \DOMDocument();
                $this->element = $this->document->createElement('root');
                $this->document->appendChild($this->element);
            }
        };
    }

    public function testGetDocument()
    {
        $document = $this->abstractRequest->getDocument();
        $this->assertInstanceOf(\DOMDocument::class, $document);
    }

    public function testGetElement()
    {
        $element = $this->abstractRequest->getElement();
        $this->assertInstanceOf(\DOMElement::class, $element);
        $this->assertEquals('root', $element->nodeName);
    }

    public function testDocumentProperty()
    {
        $reflection = new ReflectionClass(AbstractRequest::class);
        $property = $reflection->getProperty('document');
        $property->setAccessible(true);

        $document = $property->getValue($this->abstractRequest);
        $this->assertInstanceOf(\DOMDocument::class, $document);
    }

    public function testElementProperty()
    {
        $reflection = new ReflectionClass(AbstractRequest::class);
        $property = $reflection->getProperty('element');
        $property->setAccessible(true);

        $element = $property->getValue($this->abstractRequest);
        $this->assertInstanceOf(\DOMElement::class, $element);
        $this->assertEquals('root', $element->nodeName);
    }
}
