<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action;

use OxidSolutionCatalysts\TeleCash\IPG\TeleCashConstants;

/**
 * Class ConfirmRecurring
 */
class ConfirmRecurring extends Validation
{
    /** @var string|null $orderId */
    private string|null $orderId;

    /**
     * @return string|null
     */
    public function getOrderId(): string|null
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
            $this->orderId = $this->firstElementByTagNSString(
                $responseDoc,
                TeleCashConstants::NAMESPACE_IPGAPI,
                'OrderId'
            );
        } else {
            $this->errorMessage = $this->firstElementByTagNSString(
                $responseDoc,
                TeleCashConstants::NAMESPACE_A1,
                'ErrorMessage'
            );
            $this->orderId = null;
        }
    }
}
