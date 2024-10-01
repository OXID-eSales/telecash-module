<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;

use OxidSolutionCatalysts\TeleCash\IPG\API\Model\DataStorageItem;
use Prophecy\Prophet;

/**
 * Test case for Request/Action/StoreHostedData
 */
class StoreHostedDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param DataStorageItem $storageItem
     *
     * @dataProvider dataProvider
     */
    public function testXMLGeneration(DataStorageItem $storageItem)
    {
        $prophet = new Prophet();
        $orderService  = $prophet->prophesize('OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService');

        $store    = new StoreHostedData($orderService->reveal(), $storageItem);
        $document = $store->getDocument();
        $document->appendChild($store->getElement());

        $elementStore = $document->getElementsByTagName('ns2:StoreHostedData');
        $this->assertEquals(1, $elementStore->length, 'Expected element StoreHostedData not found');

        $children = [];
        /** @var \DOMNode $child */
        foreach ($elementStore->item(0)->childNodes as $child) {
            $children[$child->nodeName] = $child->nodeValue;
        }

        $this->assertArrayHasKey('ns2:DataStorageItem', $children, 'Expected element DataStorageItem not found');
        //no need to further test DataStorageItem, as this is already covered in DataStorageItemTest
    }

    /**
     * Provides some test values
     *
     * @return array
     */
    public static function dataProvider(): array
    {
        return [
            [new DataStorageItem('abc-def')]
        ];
    }
}
