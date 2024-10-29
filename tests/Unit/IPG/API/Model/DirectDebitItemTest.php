<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Model\Tests;

use OxidSolutionCatalysts\TeleCash\IPG\API\Model\DirectDebitItem;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\DirectDebitData;
use PHPUnit\Framework\TestCase;

class DirectDebitItemTest extends TestCase
{
    private DirectDebitData $directDebitData;
    private \DOMDocument $mockDocument;
    private \DOMElement $mockDirectDebitElement;

    protected function setUp(): void
    {
        // Create mock for DirectDebitData
        $this->directDebitData = $this->createMock(DirectDebitData::class);

        // Create real DOM elements for testing
        $this->mockDocument = new \DOMDocument();
        $this->mockDirectDebitElement = $this->mockDocument->createElement('ns1:DirectDebitData');

        // Setup DirectDebitData mock to return our test element
        $this->directDebitData->method('getXML')
            ->willReturn($this->mockDirectDebitElement);
    }

    public function testConstructorAndBasicXMLGeneration(): void
    {
        $directDebitItem = new DirectDebitItem(
            $this->directDebitData,
            'TEST-ID-123'
        );

        $xml = $directDebitItem->getXML($this->mockDocument);

        // Check basic structure
        $this->assertEquals('ns2:DataStorageItem', $xml->nodeName);

        // Check HostedDataID
        $hostedDataId = $xml->getElementsByTagName('ns2:HostedDataID')->item(0);
        $this->assertNotNull($hostedDataId);
        $this->assertEquals('TEST-ID-123', $hostedDataId->textContent);

        // Verify DirectDebitData was called and integrated
        $this->assertContains($this->mockDirectDebitElement, iterator_to_array($xml->childNodes));
    }

    public function testXMLGenerationWithAllOptionalParameters(): void
    {
        $directDebitItem = new DirectDebitItem(
            $this->directDebitData,
            'TEST-ID-123',
            'store',
            'true'
        );

        $xml = $directDebitItem->getXML($this->mockDocument);

        // Check function element
        $function = $xml->getElementsByTagName('ns2:Function')->item(0);
        $this->assertNotNull($function);
        $this->assertEquals('store', $function->textContent);

        // Check decline duplicates element
        $declineDuplicates = $xml->getElementsByTagName('ns2:DeclineHostedDataDuplicates')->item(0);
        $this->assertNotNull($declineDuplicates);
        $this->assertEquals('true', $declineDuplicates->textContent);
    }

    public function testXMLGenerationWithoutOptionalParameters(): void
    {
        $directDebitItem = new DirectDebitItem(
            $this->directDebitData,
            'TEST-ID-123'
        );

        $xml = $directDebitItem->getXML($this->mockDocument);

        // Optional elements should not exist
        $this->assertNull($xml->getElementsByTagName('ns2:Function')->item(0));
        $this->assertNull($xml->getElementsByTagName('ns2:DeclineHostedDataDuplicates')->item(0));
    }

    public function testElementOrder(): void
    {
        $directDebitItem = new DirectDebitItem(
            $this->directDebitData,
            'TEST-ID-123',
            'store',
            'true'
        );

        $xml = $directDebitItem->getXML($this->mockDocument);
        $children = iterator_to_array($xml->childNodes);

        // Check the order of elements
        $expectedOrder = ['ns2:Function', 'ns2:DeclineHostedDataDuplicates', 'ns1:DirectDebitData', 'ns2:HostedDataID'];
        $actualOrder = array_map(fn($node) => $node->nodeName, $children);

        // Remove any null elements that might not be present
        $actualOrder = array_filter($actualOrder);

        $this->assertEquals(
            array_values(array_intersect($expectedOrder, $actualOrder)),
            array_values($actualOrder)
        );
    }

    public function testNullableParameters(): void
    {
        // Test with null values for optional parameters
        $directDebitItem = new DirectDebitItem(
            $this->directDebitData,
            'TEST-ID-123',
            null,
            null
        );

        $xml = $directDebitItem->getXML($this->mockDocument);

        // Verify optional elements are not present
        $this->assertNull($xml->getElementsByTagName('ns2:Function')->item(0));
        $this->assertNull($xml->getElementsByTagName('ns2:DeclineHostedDataDuplicates')->item(0));

        // But required elements are still there
        $this->assertNotNull($xml->getElementsByTagName('ns2:HostedDataID')->item(0));
        $this->assertContains($this->mockDirectDebitElement, iterator_to_array($xml->childNodes));
    }
}
