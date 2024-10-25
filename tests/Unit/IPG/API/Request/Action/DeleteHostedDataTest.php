<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;

use OxidSolutionCatalysts\TeleCash\IPG\API\Model\DataStorageItem;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;

/**
 * Test class for DeleteHostedData request action
 *
 * This test verifies the XML generation for the DeleteHostedData request,
 * which is used to remove stored payment data from the TeleCash system.
 */
class DeleteHostedDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests the XML generation for the DeleteHostedData request
     *
     * This test ensures that:
     * 1. The XML structure is correctly generated
     * 2. The StoreHostedData element is present
     * 3. The DataStorageItem is properly included
     *
     * @param DataStorageItem $storageItem The storage item to be included in the request
     * @dataProvider dataProvider
     */
    public function testXMLGeneration(DataStorageItem $storageItem)
    {
        // Create a mock for the OrderService
        // In this case, we don't need to define any behavior as the service
        // is only used for XML document initialization
        $orderService = $this->createMock(OrderService::class);

        // Create the DeleteHostedData request object with our mocked service
        $delete = new DeleteHostedData($orderService, $storageItem);

        // Get the XML document and append our request element
        $document = $delete->getDocument();
        $document->appendChild($delete->getElement());

        // Verify the presence of the StoreHostedData element
        $elementStore = $document->getElementsByTagName('ns2:StoreHostedData');
        $this->assertEquals(
            1,
            $elementStore->length,
            'The XML should contain exactly one StoreHostedData element'
        );

        // Extract all child elements for verification
        $children = [];
        /** @var \DOMNode $child */
        foreach ($elementStore->item(0)->childNodes as $child) {
            $children[$child->nodeName] = $child->nodeValue;
        }

        // Verify the presence of the DataStorageItem element
        $this->assertArrayHasKey(
            'ns2:DataStorageItem',
            $children,
            'The StoreHostedData element should contain a DataStorageItem element'
        );
        // Note: Detailed testing of DataStorageItem is handled in DataStorageItemTest
    }

    /**
     * Provides test data for XML generation tests
     *
     * Currently provides:
     * - A basic DataStorageItem with ID 'abc-def'
     *
     * @return array Array of test cases containing DataStorageItem objects
     */
    public static function dataProvider(): array
    {
        return [
            [new DataStorageItem('abc-def')]
        ];
    }
}
