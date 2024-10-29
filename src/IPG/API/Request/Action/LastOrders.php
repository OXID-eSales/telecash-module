<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;

use OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action\Validation;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Error;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;

class LastOrders extends Action
{
    /**
     * @param OrderService  $service
     * @param int           $count
     * @param string|null   $orderId
     * @param string|null   $dtFrom
     * @param string|null   $dtTo
     */
    public function __construct(
        OrderService $service,
        int $count,
        string|null $orderId = null,
        string|null $dtFrom = null,
        string|null $dtTo = null
    ) {
        parent::__construct($service);

        $getLastOrders = $this->document->createElement('ns2:GetLastOrders');
        $orderCount = $this->document->createElement('ns2:Count');
        $orderCount->nodeValue = (string)$count;
        $getLastOrders->appendChild($orderCount);

        if ($orderId) {
            $oId = $this->document->createElement('ns2:OrderID');
            $oId->nodeValue = $orderId;
            $getLastOrders->appendChild($oId);
        } elseif ($dtFrom && $dtTo) {
            $dateFrom = $this->document->createElement('ns2:DateFrom');
            $dateFrom->nodeValue = $dtFrom;
            $getLastOrders->appendChild($dateFrom);

            $dateTo = $this->document->createElement('ns2:DateTo');
            $dateTo->nodeValue = $dtTo;
            $getLastOrders->appendChild($dateTo);
        }

        $item0 = $this->element->getElementsByTagName('ns2:Action')->item(0);
        if ($item0) {
            $item0->appendChild($getLastOrders);
        }
    }

    public function get(): Validation|Error
    {
        $response = $this->service->IPGApiAction($this);

        return $response instanceof Error ? $response : new Validation($response);
    }
}
