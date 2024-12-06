<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Model;

use DOMDocument;
use DOMException;
use DOMNode;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashConstants;

/**
 * Class DataStorageItem
 */
class DataStorageItem implements ElementInterface
{
    /** @var string|null  */
    protected string|null $hostedDataId;
    /** @var string|null  */
    protected string|null $function;
    /** @var string|null  */
    protected string|null $declineHostedDataDuplicates;


    /**
     * @param string|null $hostedDataId
     * @param string|null $function
     * @param string|null $declineHostedDataDuplicates
     */
    public function __construct(
        string|null $hostedDataId,
        string|null $function = null,
        string|null $declineHostedDataDuplicates = null
    ) {
        $this->hostedDataId                = $hostedDataId;
        $this->function                    = $function;
        $this->declineHostedDataDuplicates = $declineHostedDataDuplicates;
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

        $dataId              = $document->createElement(TeleCashConstants::PREF_A1 . 'HostedDataID');
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

        $xml->appendChild($dataId);

        return $xml;
    }

    /**
     * @param string $function
     */
    public function setFunction(string $function): void
    {
        $this->function = $function;
    }
}
