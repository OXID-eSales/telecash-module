<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\IPG\API\Model;

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
        $this->document = new \DOMDocument();

        // Create mock for DirectDebitData
        $this->directDebitData = $this->createMock(DirectDebitData::class);

        // Create test element with correct namespace
        $this->directDebitElement = $this->document->createElement('ns1:DirectDebitData');

        // Configure mock
        $this->directDebitData->method('getXML')
            ->willReturn($this->directDebitElement);
    }

    public function testConstructorAndBasicXMLGeneration(): void
    {
        $directDebitItem = new DirectDebitItem(
            $this->directDebitData,
            'TEST-ID-123'
        );

        $xml = $directDebitItem->getXML($this->document);

        $this->assertEquals('ns2:DataStorageItem', $xml->nodeName);

        $hostedDataId = $xml->getElementsByTagName('ns2:HostedDataID')->item(0);
        $this->assertNotNull($hostedDataId, 'HostedDataID element should exist');
        $this->assertEquals('TEST-ID-123', $hostedDataId->textContent);

        // Verify DirectDebitData is included
        $this->assertContains(
            $this->directDebitElement,
            iterator_to_array($xml->childNodes),
            'DirectDebitData element should be included'
        );
    }


    public function testXMLGenerationWithAllOptionalParameters(): void
    {
        $directDebitItem = new DirectDebitItem(
            $this->directDebitData,
            'TEST-ID-123',
            'store',
            'true'
        );

        $xml = $directDebitItem->getXML($this->document);

        // Test Function element
        $function = $xml->getElementsByTagName('ns2:Function')->item(0);
        $this->assertNotNull($function, 'Function element should exist');
        $this->assertEquals('store', $function->textContent);

        // Test DeclineHostedDataDuplicates element
        $declineDuplicates = $xml->getElementsByTagName('ns2:DeclineHostedDataDuplicates')->item(0);
        $this->assertNotNull($declineDuplicates, 'DeclineHostedDataDuplicates element should exist');
        $this->assertEquals('true', $declineDuplicates->textContent);
    }

    public function testXMLGenerationWithoutOptionalParameters(): void
    {
        $directDebitItem = new DirectDebitItem(
            $this->directDebitData,
            'TEST-ID-123'
        );

        $xml = $directDebitItem->getXML($this->document);

        // Verify optional elements don't exist
        $this->assertNull(
            $xml->getElementsByTagName('ns2:Function')->item(0),
            'Function element should not exist'
        );
        $this->assertNull(
            $xml->getElementsByTagName('ns2:DeclineHostedDataDuplicates')->item(0),
            'DeclineHostedDataDuplicates element should not exist'
        );
    }

    public function testElementOrder(): void
    {
        $directDebitItem = new DirectDebitItem(
            $this->directDebitData,
            'TEST-ID-123',
            'store',
            'true'
        );

        $xml = $directDebitItem->getXML($this->document);
        $children = iterator_to_array($xml->childNodes);

        $expectedOrder = [
            'ns2:Function',
            'ns2:DeclineHostedDataDuplicates',
            'ns1:DirectDebitData',
            'ns2:HostedDataID'
        ];

        $actualOrder = array_map(
            fn($node) => $node->nodeName,
            array_filter($children)
        );

        $this->assertEquals($expectedOrder, $actualOrder, 'Elements should be in correct order');
    }

    public function testNullableParameters(): void
    {
        $directDebitItem = new DirectDebitItem(
            $this->directDebitData,
            'TEST-ID-123',
            null,
            null
        );

        $xml = $directDebitItem->getXML($this->document);

        // Required elements should exist
        $hostedDataId = $xml->getElementsByTagName('ns2:HostedDataID')->item(0);
        $this->assertNotNull($hostedDataId, 'HostedDataID element should exist');
        $this->assertEquals('TEST-ID-123', $hostedDataId->textContent);

        // DirectDebitData should be included
        $this->assertContains(
            $this->directDebitElement,
            iterator_to_array($xml->childNodes),
            'DirectDebitData element should be included'
        );

        // Optional elements should not exist
        $this->assertNull(
            $xml->getElementsByTagName('ns2:Function')->item(0),
            'Function element should not exist'
        );
        $this->assertNull(
            $xml->getElementsByTagName('ns2:DeclineHostedDataDuplicates')->item(0),
            'DeclineHostedDataDuplicates element should not exist'
        );
    }
}
