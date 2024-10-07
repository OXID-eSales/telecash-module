<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action;

use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;

/**
 * Class Confirm
 */
class Confirm extends Action
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
        $error          = $responseDoc->getElementsByTagNameNS(OrderService::NAMESPACE_N2, 'Error');

        if ($actionResponse->length > 0 && $success === 'true') {
            if ($error->length === 0) {
                $this->wasSuccessful = true;
            } else {
                $this->errorMessage = $this->firstElementByTagNSString(
                    $responseDoc,
                    OrderService::NAMESPACE_N2,
                    'ErrorMessage'
                );
            }
        } else {
            throw new \Exception("Call failed " . $responseDoc->saveXML());
        }
    }
}
