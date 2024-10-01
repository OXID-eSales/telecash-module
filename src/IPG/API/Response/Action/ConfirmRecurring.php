<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action;

use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;

/**
 * Class ConfirmRecurring
 */
class ConfirmRecurring extends Validation
{
    /** @var string $orderId */
    private string $orderId;

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * @param \DOMDocument $responseDoc
     *
     * @throws \Exception
     */
    public function __construct(\DOMDocument $responseDoc)
    {
        parent::__construct($responseDoc);

        if ($this->wasSuccessful()) {
            $this->orderId = $this->firstElementByTagNSString($responseDoc, OrderService::NAMESPACE_N3, 'OrderId');
        } else {
            $this->errorMessage = $this->firstElementByTagNSString($responseDoc, OrderService::NAMESPACE_N2, 'ErrorMessage');
        }
    }
}
