<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Model;

/**
 * Class Payment
 */
class Payment implements ElementInterface
{
    public const CURRENCY_EUR = "978";

    /** @var string|null $hostedDataId */
    private string|null $hostedDataId;
    /** @var float|null $amount */
    private float|null $amount;

    /**
     * @param string|null $hostedDataId
     * @param float|null  $amount
     */
    public function __construct(string|null $hostedDataId = null, float|null $amount = null)
    {
        $this->hostedDataId = $hostedDataId;
        $this->amount       = $amount;
    }

    /**
     * @param \DOMDocument $document
     *
     * @return mixed
     */
    public function getXML(\DOMDocument $document): mixed
    {
        $xml = $document->createElement('ns1:Payment');

        if (!empty($this->hostedDataId)) {
            $hostedDataId = $document->createElement('ns1:HostedDataID');
            $hostedDataId->textContent = $this->hostedDataId;

            $xml->appendChild($hostedDataId);
        }

        if (!empty($this->amount)) {
            $amount                = $document->createElement('ns1:ChargeTotal');
            $amount->textContent   = (string)$this->amount;
            $currency              = $document->createElement('ns1:Currency');
            $currency->textContent = self::CURRENCY_EUR;

            $xml->appendChild($amount);
            $xml->appendChild($currency);
        }

        return $xml;
    }
}
