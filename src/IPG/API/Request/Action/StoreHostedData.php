<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;

use DOMException;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\DataStorageItem;
use OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action\Confirm;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Error;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashConstants;

/**
 * Class StoreHostedData
 */
class StoreHostedData extends Action
{
    /**
     * @param OrderService $service
     * @param DataStorageItem $storageItem
     * @throws DOMException
     */
    public function __construct(OrderService $service, DataStorageItem $storageItem)
    {
        parent::__construct($service);

        $xml         = $this->document->createElement(TeleCashConstants::PREF_A1 . 'StoreHostedData');
        $storageData = $storageItem->getXML($this->document);
        $xml->appendChild($storageData);
        $item0 = $this->element->getElementsByTagName(TeleCashConstants::PREF_A1 . 'Action')->item(0);
        $item0?->appendChild($xml);
    }

    /**
     * @return Confirm|Error
     * @throws \Exception
     */
    public function store(): Confirm|Error
    {
        $response = $this->service->IPGApiAction($this);

        return ($response instanceof Error) ? $response : new Confirm($response);
    }
}
