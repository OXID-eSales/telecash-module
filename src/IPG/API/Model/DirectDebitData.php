<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Model;

/**
 * Class DirectDebitData
 */
class DirectDebitData implements ElementInterface
{
    private string $namespaceShort = 'ns2';

    /** @var string|null $bankCode */
    private string|null $bankCode;

    /** @var string|null $accountNumber */
    private string|null $accountNumber;

    /** @var string|null $iBAN */
    private string|null $iBAN;


    public function __construct(
        string|null $iBan,
        string|null $bankCode,
        string|null $accountNumber
    ) {
        $this->iBAN          = $iBan;
        $this->bankCode      = $bankCode;
        $this->accountNumber = $accountNumber;
    }

    public function setNamespaceShort(string $namespaceShort): void
    {
        $this->namespaceShort = $namespaceShort;
    }

    /**
     * @inheritDoc
     */
    public function getXML(\DOMDocument $document): mixed
    {
        $xml = $document->createElement($this->namespaceShort . ':DE_DirectDebitData');

        if (!empty($this->bankCode) && !empty($this->accountNumber)) {
            $bankCode              = $document->createElement('ns1:BankCode');
            $bankCode->textContent = (string)$this->bankCode;
            $xml->appendChild($bankCode);

            $accountNumber = $document->createElement('ns1:AccountNumber');
            $accountNumber->textContent = (string)$this->accountNumber;
            $xml->appendChild($accountNumber);
        } elseif (!empty($this->iBAN)) {
            $iBan = $document->createElement('ns1:IBAN');
            $iBan->textContent = (string)$this->iBAN;
            $xml->appendChild($iBan);
        }

        $mandateReference = $document->createElement('ns1:MandateReference');
        $mandateReference->textContent = (string) 'MandateReference';
        $xml->appendChild($mandateReference);

        $mandateType = $document->createElement('ns1:MandateType');
        $mandateType->textContent = (string) 'SINGLE';
        $xml->appendChild($mandateType);

        return $xml;
    }
}
