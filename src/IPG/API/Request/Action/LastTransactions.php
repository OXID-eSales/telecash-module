<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;

use Exception;
use OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action\Validation;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Error;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashConstants;

class LastTransactions extends Action
{
    /**
     * @param int $count
     * @param string|null $orderId
     * @param string|null $tDate
     * @param OrderService $service
     * @throws \DOMException
     */
    public function __construct(
        OrderService $service,
        int $count,
        string|null $orderId = null,
        string|null $tDate = null
    ) {
        parent::__construct($service);

        $getLastTransactions = $this->document->createElement(TeleCashConstants::PREF_A1 . 'GetLastTransactions');
        $transCount = $this->document->createElement(TeleCashConstants::PREF_A1 . 'count');
        $transCount->nodeValue = (string)$count;
        $getLastTransactions->appendChild($transCount);

        if ($orderId) {
            $lastOrderId = $this->document->createElement(TeleCashConstants::PREF_A1 . 'OrderId');
            $lastOrderId->nodeValue = $orderId;
            $getLastTransactions->appendChild($lastOrderId);
        }
        if ($tDate) {
            $lastTDate = $this->document->createElement(TeleCashConstants::PREF_A1 . 'TDate');
            $lastTDate->nodeValue = $tDate;
            $getLastTransactions->appendChild($lastTDate);
        }

        $item0 = $this->element->getElementsByTagName(TeleCashConstants::PREF_A1 . 'Action')->item(0);
        $item0?->appendChild($getLastTransactions);
    }

    /**
     * @throws Exception
     */
    public function get(): Validation|Error
    {
        $response = $this->service->IPGApiAction($this);

        return $response instanceof Error ? $response : new Validation($response);
    }
}
