<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;

use DOMException;
use Exception;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\DataStorageItem;
use OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action\Confirm;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Error;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;

/**
 * Class DeleteHostedData
 */
class DeleteHostedData extends Action
{
    /**
     * @param OrderService $service
     * @param DataStorageItem $storageItem
     * @throws DOMException
     */
    public function __construct(OrderService $service, DataStorageItem $storageItem)
    {
        parent::__construct($service);

        // Configure the storage item
        $storageItem->setFunction('delete');

        try {
            // Get the Action element
            $actionElement = $this->element->getElementsByTagName('ns2:Action')->item(0);
            if (!$actionElement) {
                throw new \RuntimeException('Action element not found');
            }

            // Create StoreHostedData element
            $storeElement = $this->document->createElement('ns2:StoreHostedData');

            // Create and append the storage data
            $storageData = $storageItem->getXML($this->document);
            $storeElement->appendChild($storageData);

            // Append StoreHostedData to Action element
            $actionElement->appendChild($storeElement);
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to create DeleteHostedData XML: ' . $e->getMessage());
        }
    }

    /**
     * @return Confirm|Error
     * @throws Exception
     */
    public function delete(): Confirm|Error
    {
        $response = $this->service->IPGApiAction($this);
        return $response instanceof Error ? $response : new Confirm($response);
    }
}
