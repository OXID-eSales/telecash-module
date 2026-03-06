<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action;

use DOMDocument;
use Exception;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashConstants;
use RuntimeException;

/**
 * Class Validation
 */
class Validation extends Action
{
    /**
     * @param DOMDocument $responseDoc
     *
     * @throws Exception
     */
    public function __construct(DOMDocument $responseDoc)
    {
        $actionResponse = $responseDoc->getElementsByTagNameNS(
            TeleCashConstants::NAMESPACE_IPGAPI,
            'IPGApiActionResponse'
        );
        $success = $this->firstElementByTagNSString(
            $responseDoc,
            TeleCashConstants::NAMESPACE_IPGAPI,
            'successfully'
        );

        if ($actionResponse->length > 0) {
            $this->wasSuccessful = ($success === 'true');
            if (false === $this->wasSuccessful) {
                $this->errorMessage = $this->firstElementByTagNSString(
                    $responseDoc,
                    TeleCashConstants::NAMESPACE_A1,
                    'ErrorMessage'
                );
            }
        } else {
            throw new RuntimeException("Validate Call failed: no IPGApiActionResponse found");
        }
    }
}
