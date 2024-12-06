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
    /** @var DirectDebitData */
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
        $xml = $document->createElement(TeleCashConstants::PREF_A1 . 'DataStorageItem');

        $ccData = $this->directDebitData->getXML($document);
        $dataId = $document->createElement(TeleCashConstants::PREF_A1 . 'HostedDataID');
        $dataId->textContent = (string)$this->hostedDataId;

        if ($this->function !== null) {
            $function = $document->createElement(TeleCashConstants::PREF_A1 . 'Function');
            $function->textContent = $this->function;
            $xml->appendChild($function);
        }
        if ($this->declineHostedDataDuplicates !== null) {
            $declineDuplicates = $document->createElement(TeleCashConstants::PREF_A1 . 'DeclineHostedDataDuplicates');
            $declineDuplicates->textContent = $this->declineHostedDataDuplicates;
            $xml->appendChild($declineDuplicates);
        }

        $xml->appendChild($ccData);
        $xml->appendChild($dataId);

        return $xml;
    }
}
