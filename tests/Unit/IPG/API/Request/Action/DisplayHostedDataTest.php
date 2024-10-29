<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;

use OxidSolutionCatalysts\TeleCash\IPG\API\Model\DataStorageItem;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;

/**
 * Test class for DisplayHostedData request action
 *
 * This test suite verifies the XML generation functionality for the DisplayHostedData request,
 * which is used to retrieve stored payment data from the TeleCash system.
 */
class DisplayHostedDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests the XML generation for the DisplayHostedData request
     *
     * Verifies that:
     * 1. The XML document structure is correctly generated
     * 2. The StoreHostedData element exists and is properly formatted
     * 3. The DataStorageItem is correctly included in the request
     *
     * @param DataStorageItem $storageItem The storage item to be included in the request
     * @dataProvider         dataProvider
     */
    public function testXMLGeneration(DataStorageItem $storageItem): void
    {
        // Create a mock for the OrderService
        // The service is primarily used for XML document initialization
        $orderService = $this->createMock(OrderService::class);

        // Initialize the DisplayHostedData request with our test dependencies
        $display = new DisplayHostedData($orderService, $storageItem);

        // Generate and populate the XML document
        $document = $display->getDocument();
        $document->appendChild($display->getElement());

        // Verify the presence and count of StoreHostedData elements
        $elementStore = $document->getElementsByTagName('ns2:StoreHostedData');
        $this->assertEquals(
            1,
            $elementStore->length,
            'The XML should contain exactly one StoreHostedData element'
        );

        // Extract and store all child elements for verification
        $children = [];
        /** @var \DOMNode $child */
        foreach ($elementStore->item(0)->childNodes as $child) {
            $children[$child->nodeName] = $child->nodeValue;
        }

        // Verify the presence of the DataStorageItem element
        $this->assertArrayHasKey(
            'ns2:DataStorageItem',
            $children,
            'The StoreHostedData element must contain a DataStorageItem element'
        );
        // Note: Detailed testing of DataStorageItem structure is covered in DataStorageItemTest
    }

    /**
     * Provides test data for XML generation tests
     *
     * @return array<array<DataStorageItem>> Array of test cases, each containing a DataStorageItem
     */
    public static function dataProvider(): array
    {
        return [
            [new DataStorageItem('abc-def')] // Basic storage item test case
        ];
    }
}
