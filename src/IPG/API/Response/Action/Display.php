<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action;

use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashConstants;
use RuntimeException;

/**
 * Class Display
 */
class Display extends Action
{
    /**
     * @var string|null
     */
    protected string|null $ccNumber;

    /**
     * @var string|null
     */
    protected string|null $ccValid;

    /**
     * @var string|null
     */
    protected string|null $hostedDataId;

    /**
     * @return string|null
     */
    public function getCCNumber(): string|null
    {
        return $this->ccNumber;
    }

    /**
     * @return string|null
     */
    public function getCCValid(): string|null
    {
        return $this->ccValid;
    }

    /**
     * @return string|null
     */
    public function getHostedDataId(): string|null
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
        $this->ccNumber = null;
        $this->ccValid = null;
        $this->hostedDataId = null;

        $actionResponse = $responseDoc->getElementsByTagNameNS(
            TeleCashConstants::NAMESPACE_IPGAPI,
            'IPGApiActionResponse'
        );
        $success = $this->firstElementByTagNSString(
            $responseDoc,
            TeleCashConstants::NAMESPACE_IPGAPI,
            'successfully'
        );
        $error = $responseDoc->getElementsByTagNameNS(
            TeleCashConstants::NAMESPACE_A1,
            'Error'
        );

        if ($actionResponse->length > 0 && $success === 'true') {
            if ($error->length === 0) {
                $this->wasSuccessful = true;
                $ccData = $responseDoc->getElementsByTagNameNS(
                    TeleCashConstants::NAMESPACE_A1,
                    'CreditCardData'
                );
                if ($ccData->length > 0) {
                    $this->ccNumber = $this->firstElementByTagNSString(
                        $responseDoc,
                        TeleCashConstants::NAMESPACE_V1,
                        'CardNumber'
                    );
                    $expMonth = $this->firstElementByTagNSString(
                        $responseDoc,
                        TeleCashConstants::NAMESPACE_V1,
                        'ExpMonth'
                    );
                    $expYear = $this->firstElementByTagNSString(
                        $responseDoc,
                        TeleCashConstants::NAMESPACE_V1,
                        'ExpYear'
                    );
                    $this->ccValid = $expMonth . '/' . $expYear;
                    $this->hostedDataId = $this->firstElementByTagNSString(
                        $responseDoc,
                        TeleCashConstants::NAMESPACE_A1,
                        'HostedDataID'
                    );
                }
            } else {
                $this->errorMessage = $this->firstElementByTagNSString(
                    $responseDoc,
                    TeleCashConstants::NAMESPACE_A1,
                    'ErrorMessage'
                );
            }
        } else {
            throw new RuntimeException("Display Call failed " . $responseDoc->saveXML());
        }
    }
}
