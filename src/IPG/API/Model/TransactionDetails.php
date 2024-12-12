<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Model;

use DOMDocument;
use DOMException;
use DOMNode;

class TransactionDetails implements ElementInterface
{
    /**
     * TransactionDetails constructor.
     *
     * @param string|null $namespace
     * @param string|null $comments
     * @param string|null $invoiceNumber
     */
    public function __construct(
        private readonly ?string $namespace,
        private readonly ?string $comments,
        private readonly ?string $invoiceNumber = null
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
        // Create root element with namespace
        $xml = $document->createElement($this->namespace . ':TransactionDetails');

        // Helper function to create elements
        $addElement = function (string $name, ?string $value) use ($document, $xml) {
            if ($value !== null) {
                $element = $document->createElement('ns1:' . $name);
                $element->textContent = $value;
                $xml->appendChild($element);
            }
        };

        // Add required Comments element
        $addElement('Comments', (string)$this->comments);

        // Add optional InvoiceNumber if present
        if (!empty($this->invoiceNumber)) {
            $addElement('InvoiceNumber', $this->invoiceNumber);
        }

        return $xml;
    }
}
