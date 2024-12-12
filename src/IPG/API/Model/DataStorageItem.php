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
    protected ?string $hostedDataId;
    protected ?string $function;
    protected ?string $declineHostedDataDuplicates;

    public function __construct(
        ?string $hostedDataId,
        ?string $function = null,
        ?string $declineHostedDataDuplicates = null
    ) {
        $this->hostedDataId = $hostedDataId;
        $this->function = $function;
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
        $xml = $document->createElement('ns2:DataStorageItem');

        // Helper function to create elements
        $addElement = function (string $name, ?string $value) use ($document, $xml) {
            if ($value !== null) {
                $element = $document->createElement('ns2:' . $name);
                $element->textContent = $value;
                $xml->appendChild($element);
            }
        };

        // Add elements in order
        if ($this->function !== null) {
            $addElement('Function', $this->function);
        }

        if ($this->declineHostedDataDuplicates !== null) {
            $addElement('DeclineHostedDataDuplicates', $this->declineHostedDataDuplicates);
        }

        // HostedDataID is required
        $addElement('HostedDataID', (string)$this->hostedDataId);

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
