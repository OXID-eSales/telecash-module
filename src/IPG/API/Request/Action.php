<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request;

use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;

/**
 * Class Action
 */
class Action extends ActionRequest
{
    /**
     * @param OrderService $service
     */
    public function __construct(OrderService $service)
    {
        parent::__construct($service);
        $this->element->appendChild($this->document->createElement('ns2:Action'));
    }
}
