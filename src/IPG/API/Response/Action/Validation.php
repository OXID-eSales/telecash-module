<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action;

use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;

/**
 * Class Validation
 */
class Validation extends Action
{
    /**
     * @param \DOMDocument $responseDoc
     *
     * @throws \Exception
     */
    public function __construct(\DOMDocument $responseDoc)
    {
        $actionResponse = $responseDoc->getElementsByTagNameNS(OrderService::NAMESPACE_N3, 'IPGApiActionResponse');
        $success        = $this->firstElementByTagNSString($responseDoc, OrderService::NAMESPACE_N3, 'successfully');

        if ($actionResponse->length > 0) {
            $this->wasSuccessful = ($success === 'true');
            if (false === $this->wasSuccessful) {
                $this->errorMessage = $this->firstElementByTagNSString(
                    $responseDoc,
                    OrderService::NAMESPACE_N2,
                    'ErrorMessage'
                );
            }
        } else {
            throw new \Exception("Validate Call failed " . $responseDoc->saveXML());
        }
    }
}
