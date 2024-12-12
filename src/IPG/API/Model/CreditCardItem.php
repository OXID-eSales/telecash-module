<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Model;

use DOMDocument;
use DOMException;
use DOMNode;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashConstants;

/**
 * Class CreditCardItem
 */
class CreditCardItem extends DataStorageItem
{
    protected CreditCardData $creditCardData;

    /**
     * @param CreditCardData $creditCardData
     * @param string|null    $hostedDataId
     * @param string|null    $function
     * @param string|null    $declineHostedDataDuplicates
     */
    public function __construct(
        CreditCardData $creditCardData,
        ?string $hostedDataId,
        ?string $function = null,
        ?string $declineHostedDataDuplicates = null
    ) {
        parent::__construct($hostedDataId, $function, $declineHostedDataDuplicates);
        $this->creditCardData = $creditCardData;
    }

    /**
     * @param DOMDocument $document
     *
     * @return DOMNode
     * @throws DOMException
     */
    public function getXML(DOMDocument $document): DOMNode
    {
        $xml = $document->createElement('ns2:DataStorageItem');

        // Helper function to create elements
        $addElement = function (string $name, ?string $value) use ($document, $xml) {
            if ($value !== null) {
                $element = $document->createElement('ns2:' . $name);
                $element->textContent = $value;
                $xml->appendChild($element);
            }
        };

        // Add elements in correct order
        if ($this->function !== null) {
            $addElement('Function', $this->function);
        }

        if ($this->declineHostedDataDuplicates !== null) {
            $addElement('DeclineHostedDataDuplicates', $this->declineHostedDataDuplicates);
        }

        // Set namespace for credit card data and append it
        $this->creditCardData->setNamespaceShort('ns2');
        $xml->appendChild($this->creditCardData->getXML($document));

        // Add HostedDataID
        $addElement('HostedDataID', (string)$this->hostedDataId);

        return $xml;
    }
}
