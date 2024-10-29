<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Model;

/**
 * Class CreditCardData
 */
class CreditCardData implements ElementInterface
{
    private string $namespaceShort = 'ns2';

    /** @var string|null $CardNumber */
    private string|null $cardNumber;

    /** @var string|null $ExpMonth */
    private string|null $expMonth;

    /** @var string|null $ExpYear */
    private string|null $expYear;

    /**
     * @param string $cardNumber
     * @param string $expMonth
     * @param string $expYear
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
     * @param \DOMDocument $document
     *
     * @return mixed
     */
    public function getXML(\DOMDocument $document): mixed
    {
        $xml = $document->createElement($this->namespaceShort . ':CreditCardData');
        if (!empty($this->cardNumber)) {
            $cardNumber              = $document->createElement('ns1:CardNumber');
            $cardNumber->textContent = $this->cardNumber;
            $xml->appendChild($cardNumber);
        }
        $expMonth = $document->createElement('ns1:ExpMonth');
        $expMonth->textContent = (string)$this->expMonth;
        $expYear = $document->createElement('ns1:ExpYear');
        $expYear->textContent = (string)$this->expYear;

        $xml->appendChild($expMonth);
        $xml->appendChild($expYear);

        return $xml;
    }
}
