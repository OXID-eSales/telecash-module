<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;

use DOMException;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\DataStorageItem;
use OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action\Display;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Error;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashConstants;

/**
 * Class DisplayHostedData
 */
class DisplayHostedData extends Action
{
    /**
     * @param OrderService $service
     * @param DataStorageItem $storageItem
     * @throws DOMException
     */
    public function __construct(OrderService $service, DataStorageItem $storageItem)
    {
        parent::__construct($service);

        try {
            // Set the function for the storage item
            $storageItem->setFunction('display');

            // Get the Action element from parent
            $actionElement = $this->element->getElementsByTagName('ns2:Action')->item(0);
            if (!$actionElement) {
                throw new \RuntimeException('Action element not found');
            }

            // Create the StoreHostedData element
            $storeElement = $this->document->createElement('ns2:StoreHostedData');

            // Get and append storage data
            $storageData = $storageItem->getXML($this->document);
            $storeElement->appendChild($storageData);

            // Append to Action element
            $actionElement->appendChild($storeElement);
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to create DisplayHostedData XML: ' . $e->getMessage());
        }
    }

    /**
     * @return Display|Error
     * @throws \Exception
     */
    public function display(): Display|Error
    {
        $response = $this->service->IPGApiAction($this);
        return $response instanceof Error ? $response : new Display($response);
    }
}
