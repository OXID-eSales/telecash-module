<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Model;

use DOMDocument;
use DOMException;
use DOMNode;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashConstants;

class TransactionDetails implements ElementInterface
{
    /**
     * @var string|null $namespace
     */
    private string|null $namespace;

    /**
     * @var string|null $comments
     */
    private string|null $comments;

    /**
     * @var string|null $invoiceNumber
     */
    private string|null $invoiceNumber;

    /**
     * TransactionDetails constructor.
     *
     * @param string|null $namespace
     * @param string|null $comments
     * @param string|null $invoiceNumber
     */
    public function __construct(string|null $namespace, string|null $comments, string|null $invoiceNumber = null)
    {
        $this->namespace     = $namespace ?? TeleCashConstants::A1;
        $this->comments      = $comments;
        $this->invoiceNumber = $invoiceNumber;
    }

    /**
     * @param DOMDocument $document
     *
     * @return DOMNode
     * @throws DOMException
     */
    public function getXML(DOMDocument $document): DOMNode
    {
        $xml = $document->createElement(sprintf('%s:TransactionDetails', $this->namespace));

        $comments = $document->createElement(TeleCashConstants::PREF_V1 . 'Comments');
        $comments->textContent = (string)$this->comments;

        $xml->appendChild($comments);

        if (!empty($this->invoiceNumber)) {
            $invoiceNumber = $document->createElement(TeleCashConstants::PREF_V1 . 'InvoiceNumber');
            $invoiceNumber->textContent = (string)$this->invoiceNumber;

            $xml->appendChild($invoiceNumber);
        }

        return $xml;
    }
}
