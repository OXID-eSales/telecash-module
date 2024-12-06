<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request;

use DOMException;
use OxidSolutionCatalysts\TeleCash\IPG\API\AbstractRequest;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashConstants;

/**
 * Class ActionRequest
 */
class ActionRequest extends AbstractRequest
{
    protected OrderService $service;

    /**
     * @param OrderService $service
     * @throws DOMException
     */
    public function __construct(OrderService $service)
    {
        $this->service = $service;
        $this->document = new \DOMDocument('1.0', 'UTF-8');

        $this->element = $this->document->createElement(TeleCashConstants::PREF_IPGAPI . 'IPGApiActionRequest');
    }
}
