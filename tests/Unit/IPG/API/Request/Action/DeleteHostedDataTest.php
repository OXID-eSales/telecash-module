<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;

use DOMException;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\DataStorageItem;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;
use PHPUnit\Framework\TestCase;

/**
 * Test class for DeleteHostedData request action
 *
 * This test verifies the XML generation for the DeleteHostedData request,
 * which is used to remove stored payment data from the TeleCash system.
 */
class DeleteHostedDataTest extends TestCase
{
    private OrderService $orderService;

    protected function setUp(): void
    {
        $this->orderService = $this->createMock(OrderService::class);
    }

    /**
     * Tests the XML generation for the DeleteHostedData request
     *
     * This test ensures that:
     * 1. The XML structure is correctly generated
     * 2. The StoreHostedData element is present
     * 3. The DataStorageItem is properly included
     *
     * @param DataStorageItem $storageItem The storage item to be included in the request
     * @throws DOMException
     * @dataProvider dataProvider
     */
    public function testXMLGeneration(DataStorageItem $storageItem): void
    {
        // Create delete request
        $delete = new DeleteHostedData($this->orderService, $storageItem);

        // Get the document from the class instance
        $document = $delete->getDocument();
        $element = $delete->getElement();

        // For debugging
        // error_log($document->saveXML());

        // Verify XML structure step by step
        $actionElements = $element->getElementsByTagName('ns2:Action');
        $this->assertCount(1, $actionElements, 'Should have one Action element');

        $actionElement = $actionElements->item(0);
        $this->assertNotNull($actionElement, 'Action element should not be null');

        $storeElements = $actionElement->getElementsByTagName('ns2:StoreHostedData');
        $this->assertCount(1, $storeElements, 'Should have one StoreHostedData element');

        $storageElements = $storeElements->item(0)->getElementsByTagName('ns2:DataStorageItem');
        $this->assertCount(1, $storageElements, 'Should have one DataStorageItem element');

        // Verify function is "delete"
        $functionElements = $storageElements->item(0)->getElementsByTagName('ns2:Function');
        $this->assertCount(1, $functionElements, 'Should have one Function element');
        $this->assertEquals('delete', $functionElements->item(0)->textContent);

        // Verify hosted data ID
        $idElements = $storageElements->item(0)->getElementsByTagName('ns2:HostedDataID');
        $this->assertCount(1, $idElements, 'Should have one HostedDataID element');
        $this->assertEquals('abc-def', $idElements->item(0)->textContent);
    }

    /**
     * Provides test data for XML generation tests
     *
     * Currently, provides:
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
