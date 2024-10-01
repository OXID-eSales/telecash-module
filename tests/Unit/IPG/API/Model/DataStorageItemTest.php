<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Model;

/**
 * Test case for DataStorageItem
 *
 * @package Checkdomain\TeleCash\IPG\API\Model
 */
class DataStorageItemTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @param string|null $hostedDataId
     * @param string|null $function
     * @param string|null $declineHostedDataDuplicates
     *
     * @dataProvider dataProvider
     */
    public function testXMLGeneration(string|null $hostedDataId, string|null $function, string|null $declineHostedDataDuplicates)
    {
        $item     = new DataStorageItem($hostedDataId, $function, $declineHostedDataDuplicates);
        $document = new \DOMDocument('1.0', 'UTF-8');
        $xml      = $item->getXML($document);
        $document->appendChild($xml);

        $elementDSItem = $document->getElementsByTagName('ns2:DataStorageItem');
        $this->assertEquals(1, $elementDSItem->length, 'Expected element DataStorageItem not found');

        $children = [];
        /** @var \DOMNode $child */
        foreach ($elementDSItem->item(0)->childNodes as $child) {
            $children[$child->nodeName] = $child->nodeValue;
        }

        $this->assertArrayHasKey('ns2:HostedDataID', $children, 'Expected element HostedDataId not found');
        $this->assertEquals($hostedDataId, $children['ns2:HostedDataID'], 'Hosted data id did not match');

        if ($function !== null) {
            $this->assertArrayHasKey('ns2:Function', $children, 'Expected element Function not found');
            $this->assertEquals($function, $children['ns2:Function'], 'Function did not match');
        }

        if ($declineHostedDataDuplicates !== null) {
            $this->assertArrayHasKey('ns2:DeclineHostedDataDuplicates', $children, 'Expected element DeclineHostedDataDuplicates not found');
            $this->assertEquals($declineHostedDataDuplicates, $children['ns2:DeclineHostedDataDuplicates'], 'DeclineHostedDataDuplicates did not match');
        }
    }

    /**
     * Provides some test values
     *
     * @return array
     */
    public static function dataProvider(): array
    {
        return [
            ['abc-def', null, null],
            ['abc-def', 'display', null],
            ['abc-def', 'display', 'true']
        ];
    }
}
