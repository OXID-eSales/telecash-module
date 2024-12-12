<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Model;

use DOMDocument;
use DOMException;
use DOMNode;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashConstants;

/**
 * Class CreditCardData
 */
class CreditCardData implements ElementInterface
{
    private string $namespaceShort = TeleCashConstants::A1;

    private ?string $cardNumber;
    private ?string $expMonth;
    private ?string $expYear;

    /**
     * @param string|null $cardNumber
     * @param string|null $expMonth
     * @param string|null $expYear
     */
    public function __construct(?string $cardNumber, ?string $expMonth, ?string $expYear)
    {
        $this->cardNumber = $cardNumber;
        $this->expMonth = $expMonth;
        $this->expYear = $expYear;
    }

    public function setNamespaceShort(string $namespaceShort): void
    {
        $this->namespaceShort = $namespaceShort;
    }

    /**
     * @param DOMDocument $document
     *
     * @return DOMNode
     * @throws DOMException
     */
    public function getXML(DOMDocument $document): DOMNode
    {
        $xml = $document->createElement($this->namespaceShort . ':CreditCardData');

        // Helper function to create elements with ns1 namespace
        $addElement = function (string $name, ?string $value) use ($document, $xml) {
            if ($value !== null) {
                $element = $document->createElement('ns1:' . $name);
                $element->textContent = $value;
                $xml->appendChild($element);
            }
        };

        // Add elements in order
        if (!empty($this->cardNumber)) {
            $addElement('CardNumber', $this->cardNumber);
        }
        $addElement('ExpMonth', (string)$this->expMonth);
        $addElement('ExpYear', (string)$this->expYear);

        return $xml;
    }
}
