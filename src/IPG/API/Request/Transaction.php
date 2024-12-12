<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request;

use DOMException;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashConstants;

/**
 * Class Transaction
 */
class Transaction extends OrderRequest
{
    /**
     * @param OrderService $service
     * @throws DOMException
     */
    public function __construct(OrderService $service)
    {
        parent::__construct($service);

        $this->element->appendChild($this->document->createElement(TeleCashConstants::PREF_V1 . 'Transaction'));
    }

    /**
     * Set the transaction type
     *
     * @return \DOMElement|null
     */
    protected function getTransactionElement(): \DOMElement|null
    {
        return $this->element->getElementsByTagName(TeleCashConstants::PREF_V1 . 'Transaction')->item(0);
    }
}
