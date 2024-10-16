<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Model;

/**
 * Class DirectDebitData
 */
class DirectDebitData implements ElementInterface
{
    /** @var string|null $bankCode */
    private string|null $bankCode;

    /** @var string|null $accountNumber */
    private string|null $accountNumber;

    public function __construct(string|null $bankCode, string|null $accountNumber)
    {
        $this->bankCode      = $bankCode;
        $this->accountNumber = $accountNumber;
    }

    /**
     * @inheritDoc
     */
    public function getXML(\DOMDocument $document): mixed
    {
        $xml = $document->createElement('ns2:DE_DirectDebitData');
        $bankCode              = $document->createElement('ns3:BankCode');
        $bankCode->textContent = (string)$this->bankCode;
        $xml->appendChild($bankCode);

        $accountNumber = $document->createElement('ns3:AccountNumber');
        $accountNumber->textContent = (string)$this->accountNumber;
        $xml->appendChild($accountNumber);

        return $xml;
    }
}
