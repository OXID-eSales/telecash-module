<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Model;

use DOMDocument;
use DOMException;
use DOMNode;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashConstants;

/**
 * Class CreditCardItem
 */
class DirectDebitItem extends DataStorageItem
{
    protected DirectDebitData $directDebitData;

    /**
     * @param DirectDebitData $directDebitData
     * @param string|null    $hostedDataId
     * @param string|null    $function
     * @param string|null    $declineHostedDataDuplicates
     */
    public function __construct(
        DirectDebitData $directDebitData,
        string|null $hostedDataId,
        string|null $function = null,
        string|null $declineHostedDataDuplicates = null
    ) {
        parent::__construct($hostedDataId, $function, $declineHostedDataDuplicates);
        $this->directDebitData = $directDebitData;
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

        // Add elements in specified order
        if ($this->function !== null) {
            $function = $document->createElement('ns2:Function');
            $function->textContent = $this->function;
            $xml->appendChild($function);
        }

        if ($this->declineHostedDataDuplicates !== null) {
            $declineDuplicates = $document->createElement('ns2:DeclineHostedDataDuplicates');
            $declineDuplicates->textContent = $this->declineHostedDataDuplicates;
            $xml->appendChild($declineDuplicates);
        }

        // Add DirectDebitData
        $xml->appendChild($this->directDebitData->getXML($document));

        // Add HostedDataID
        $dataId = $document->createElement('ns2:HostedDataID');
        $dataId->textContent = (string)$this->hostedDataId;
        $xml->appendChild($dataId);

        return $xml;
    }
}
