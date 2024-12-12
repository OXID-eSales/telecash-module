<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request;

use DOMDocument;
use DOMElement;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;

/**
 * Class Action
 */
class Action extends ActionRequest
{
    protected DOMDocument $document;
    protected DOMElement $element;
    protected OrderService $service;

    public function __construct(OrderService $service)
    {
        $this->service = $service;
        $this->document = new DOMDocument('1.0', 'UTF-8');

        // Create the IPGApiActionRequest element with correct namespace
        $this->element = $this->document->createElement('ns2:IPGApiActionRequest');

        // Create and append the Action element
        $action = $this->document->createElement('ns2:Action');
        $this->element->appendChild($action);
    }
}
