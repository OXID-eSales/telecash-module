<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request;

use DOMException;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashConstants;

/**
 * Class Action
 */
class Action extends ActionRequest
{
    /**
     * @param OrderService $service
     * @throws DOMException
     */
    public function __construct(OrderService $service)
    {
        parent::__construct($service);
        $this->element->appendChild($this->document->createElement(TeleCashConstants::PREF_A1 . 'Action'));
    }
}
