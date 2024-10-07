<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;

use OxidSolutionCatalysts\TeleCash\IPG\API\Model\DataStorageItem;
use OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action\Display;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Error;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;

/**
 * Class DisplayHostedData
 */
class DisplayHostedData extends Action
{
    /**
     * @param OrderService    $service
     * @param DataStorageItem $storageItem
     */
    public function __construct(OrderService $service, DataStorageItem $storageItem)
    {
        parent::__construct($service);

        $storageItem->setFunction("display");
        $xml         = $this->document->createElement('ns2:StoreHostedData');
        $storageData = $storageItem->getXML($this->document);
        $xml->appendChild($storageData);
        $item0 = $this->element->getElementsByTagName('ns2:Action')->item(0);
        if ($item0) {
            $item0->appendChild($xml);
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
