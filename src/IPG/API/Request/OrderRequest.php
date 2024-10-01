<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request;

use OxidSolutionCatalysts\TeleCash\IPG\API\AbstractRequest;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;

/**
 * Class OrderRequest
 */
class OrderRequest extends AbstractRequest
{
    protected OrderService $service;

    /**
     * @param OrderService $service
     */
    public function __construct(OrderService $service)
    {
        $this->service = $service;
        $this->document = new \DOMDocument('1.0', 'UTF-8');

        $this->element = $this->document->createElement('ns3:IPGApiOrderRequest');
    }
}
