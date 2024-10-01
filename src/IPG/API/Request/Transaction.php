<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request;

use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;

/**
 * Class Transaction
 */
class Transaction extends OrderRequest
{
    /**
     * @param OrderService $service
     */
    public function __construct(OrderService $service)
    {
        parent::__construct($service);

        $this->element->appendChild($this->document->createElement('ns1:Transaction'));
    }

    /**
     * Set the transaction type
     *
     * @return \DOMElement
     */
    protected function getTransactionElement(): \DOMElement
    {
        return $this->element->getElementsByTagName('ns1:Transaction')->item(0);
    }
}
