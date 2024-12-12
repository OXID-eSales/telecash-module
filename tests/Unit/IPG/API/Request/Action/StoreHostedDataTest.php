<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;

use DOMException;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\DataStorageItem;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Test class for StoreHostedData request action
 *
 * Validates the XML generation for storing payment data in the TeleCash system.
 * This test ensures proper formatting of the storage request XML structure.
 */
class StoreHostedDataTest extends TestCase
{
    /**
     * Tests the XML generation for the StoreHostedData request
     *
     * Verifies that:
     * 1. The StoreHostedData element is present
     * 2. The DataStorageItem is properly included
     * 3. The XML structure follows the required format
     *
     * @param DataStorageItem $storageItem The storage item to be stored
     * @throws DOMException
     * @throws Exception
     * @dataProvider dataProvider
     */
    public function testXMLGeneration(DataStorageItem $storageItem): void
    {
        // Create mock for order service
        $orderService = $this->createMock(OrderService::class);

        // Create and build the store request
        $store = new StoreHostedData($orderService, $storageItem);
        $document = $store->getDocument();
        $document->appendChild($store->getElement());

        // Verify StoreHostedData element exists
        $elementStore = $document->getElementsByTagName('ns2:StoreHostedData');
        $this->assertEquals(
            1,
            $elementStore->length,
            'XML must contain exactly one StoreHostedData element'
        );

        // Extract child elements for verification
        $children = [];
        /** @var \DOMNode $child */
        foreach ($elementStore->item(0)->childNodes as $child) {
            $children[$child->nodeName] = $child->nodeValue;
        }

        // Verify DataStorageItem presence
        $this->assertArrayHasKey(
            'ns2:DataStorageItem',
            $children,
            'StoreHostedData must contain DataStorageItem element'
        );
        // Detailed DataStorageItem testing is covered in DataStorageItemTest
    }

    /**
     * Provides test data for store request testing
     *
     * @return array Array of test cases with DataStorageItem objects
     */
    public static function dataProvider(): array
    {
        return [
            [new DataStorageItem('abc-def')] // Basic storage item test case
        ];
    }
}
