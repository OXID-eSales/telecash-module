<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action;

use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;

/**
 * Class Display
 */
class Display extends Action
{
    /**
     * @var string
     */
    protected string $ccNumber;

    /**
     * @var string
     */
    protected string $ccValid;

    /**
     * @var string
     */
    protected string $hostedDataId;

    /**
     * @return string
     */
    public function getCCNumber(): string
    {
        return $this->ccNumber;
    }

    /**
     * @return string
     */
    public function getCCValid(): string
    {
        return $this->ccValid;
    }

    /**
     * @return string
     */
    public function getHostedDataId(): string
    {
        return $this->hostedDataId;
    }

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
                $ccData = $responseDoc->getElementsByTagNameNS(OrderService::NAMESPACE_N2, 'CreditCardData');
                if ($ccData->length > 0) {
                    $this->ccNumber     = $this->firstElementByTagNSString(
                        $responseDoc,
                        OrderService::NAMESPACE_N1,
                        'CardNumber'
                    );
                    $expMonth           = $this->firstElementByTagNSString(
                        $responseDoc,
                        OrderService::NAMESPACE_N1,
                        'ExpMonth'
                    );
                    $expYear            = $this->firstElementByTagNSString(
                        $responseDoc,
                        OrderService::NAMESPACE_N1,
                        'ExpYear'
                    );
                    $this->ccValid      = $expMonth . '/' . $expYear;
                    $this->hostedDataId = $this->firstElementByTagNSString(
                        $responseDoc,
                        OrderService::NAMESPACE_N2,
                        'HostedDataID'
                    );
                }
            } else {
                $this->errorMessage = $this->firstElementByTagNSString(
                    $responseDoc,
                    OrderService::NAMESPACE_N2,
                    'ErrorMessage'
                );
            }
        } else {
            throw new \Exception("Display Call failed " . $responseDoc->saveXML());
        }
    }
}
