<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Model;

use DOMDocument;
use DOMException;
use DOMNode;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashConstants;

/**
 * Class DirectDebitData
 */
class DirectDebitData implements ElementInterface
{
    private string $namespaceShort = TeleCashConstants::A1;

    private ?string $bankCode;
    private ?string $accountNumber;
    private ?string $iBAN;

    public function __construct(
        ?string $iBan,
        ?string $bankCode,
        ?string $accountNumber
    ) {
        $this->iBAN = $iBan;
        $this->bankCode = $bankCode;
        $this->accountNumber = $accountNumber;
    }

    public function setNamespaceShort(string $namespaceShort): void
    {
        $this->namespaceShort = $namespaceShort;
    }

    /**
     * @inheritDoc
     * @throws DOMException
     */
    public function getXML(DOMDocument $document): DOMNode
    {
        // Create root element with current namespace
        $xml = $document->createElement($this->namespaceShort . ':DE_DirectDebitData');

        // Helper function to create elements
        $addElement = function (string $name, ?string $value) use ($document, $xml) {
            if ($value !== null) {
                $element = $document->createElement('ns1:' . $name);
                $element->textContent = $value;
                $xml->appendChild($element);
                return $element;
            }
            return null;
        };

        // Add bank data elements based on provided information
        if (!empty($this->bankCode) && !empty($this->accountNumber)) {
            $addElement('BankCode', $this->bankCode);
            $addElement('AccountNumber', $this->accountNumber);
        } elseif (!empty($this->iBAN)) {
            $addElement('IBAN', $this->iBAN);
        }

        // Add required elements
        $addElement('MandateReference', 'MandateReference');
        $addElement('MandateType', 'SINGLE');

        return $xml;
    }
}
