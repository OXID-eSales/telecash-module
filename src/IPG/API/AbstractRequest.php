<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API;

/**
 * Class Request
 */
abstract class AbstractRequest
{

    /** @var \DOMDocument */
    protected \DOMDocument $document;

    /** @var \DOMElement */
    protected \DOMElement $element;

    /**
     * @return \DOMDocument
     */
    public function getDocument(): \DOMDocument
    {
        return $this->document;
    }

    /**
     * @return \DOMElement
     */
    public function getElement(): \DOMElement
    {
        return $this->element;
    }
}
