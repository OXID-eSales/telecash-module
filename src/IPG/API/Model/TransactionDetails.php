<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Model;

class TransactionDetails implements ElementInterface
{
    /**
     * @var string|null $namespace
     */
    private string|null $namespace;

    /**
     * @var string $comments
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
        $this->namespace     = $namespace ?? 'ns2';
        $this->comments      = $comments;
        $this->invoiceNumber = $invoiceNumber;
    }

    /**
     * @param \DOMDocument $document
     *
     * @return mixed
     */
    public function getXML(\DOMDocument $document): mixed
    {
        $xml = $document->createElement(sprintf('%s:TransactionDetails', $this->namespace));

        $comments = $document->createElement('ns1:Comments');
        $comments->textContent = (string)$this->comments;

        $xml->appendChild($comments);

        if (!empty($this->invoiceNumber)) {
            $invoiceNumber = $document->createElement('ns1:InvoiceNumber');
            $invoiceNumber->textContent = (string)$this->invoiceNumber;

            $xml->appendChild($invoiceNumber);
        }

        return $xml;
    }
}
