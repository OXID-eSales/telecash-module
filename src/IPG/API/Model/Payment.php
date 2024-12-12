<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Model;

use DOMDocument;
use DOMException;
use DOMNode;

/**
 * Class Payment
 */
class Payment implements ElementInterface
{
    public const CURRENCY_EUR = "978";

    public function __construct(
        private readonly ?string $hostedDataId = null,
        private readonly ?float $amount = null
    ) {
    }

    /**
     * @param DOMDocument $document
     *
     * @return DOMNode
     * @throws DOMException
     */
    public function getXML(DOMDocument $document): DOMNode
    {
        $xml = $document->createElement('ns1:Payment');

        // Helper function to create elements
        $addElement = function (string $name, ?string $value) use ($document, $xml) {
            if ($value !== null) {
                $element = $document->createElement('ns1:' . $name);
                $element->textContent = $value;
                $xml->appendChild($element);
            }
        };

        // Add HostedDataID if provided
        if (!empty($this->hostedDataId)) {
            $addElement('HostedDataID', $this->hostedDataId);
        }

        // Add amount and currency if amount is provided
        if (!empty($this->amount)) {
            $addElement('ChargeTotal', (string)$this->amount);
            $addElement('Currency', self::CURRENCY_EUR);
        }

        return $xml;
    }
}
