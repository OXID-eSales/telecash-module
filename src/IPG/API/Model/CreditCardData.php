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

    /** @var string|null $CardNumber */
    private string|null $cardNumber;

    /** @var string|null $ExpMonth */
    private string|null $expMonth;

    /** @var string|null $ExpYear */
    private string|null $expYear;

    /**
     * @param string|null $cardNumber
     * @param string|null $expMonth
     * @param string|null $expYear
     */
    public function __construct(string|null $cardNumber, string|null $expMonth, string|null $expYear)
    {
        $this->cardNumber = $cardNumber;
        $this->expMonth   = $expMonth;
        $this->expYear    = $expYear;
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
        if (!empty($this->cardNumber)) {
            $cardNumber              = $document->createElement(TeleCashConstants::PREF_V1 . 'CardNumber');
            $cardNumber->textContent = $this->cardNumber;
            $xml->appendChild($cardNumber);
        }
        $expMonth = $document->createElement(TeleCashConstants::PREF_V1 . 'ExpMonth');
        $expMonth->textContent = (string)$this->expMonth;
        $expYear = $document->createElement(TeleCashConstants::PREF_V1 . 'ExpYear');
        $expYear->textContent = (string)$this->expYear;

        $xml->appendChild($expMonth);
        $xml->appendChild($expYear);

        return $xml;
    }
}
