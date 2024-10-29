<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Model;

/**
 * Class CreditCardItem
 */
class DirectDebitItem extends DataStorageItem
{
    /** @var DirectDebitData */
    protected $directDebitData;

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
     * @param \DOMDocument $document
     *
     * @return mixed
     */
    public function getXML(\DOMDocument $document): mixed
    {
        $xml = $document->createElement('ns2:DataStorageItem');

        $ccData = $this->directDebitData->getXML($document);
        $dataId = $document->createElement('ns2:HostedDataID');
        $dataId->textContent = (string)$this->hostedDataId;

        if ($this->function != null) {
            $function = $document->createElement('ns2:Function');
            $function->textContent = $this->function;
            $xml->appendChild($function);
        }
        if ($this->declineHostedDataDuplicates != null) {
            $declineDuplicates = $document->createElement('ns2:DeclineHostedDataDuplicates');
            $declineDuplicates->textContent = $this->declineHostedDataDuplicates;
            $xml->appendChild($declineDuplicates);
        }

        $xml->appendChild($ccData);
        $xml->appendChild($dataId);

        return $xml;
    }
}
