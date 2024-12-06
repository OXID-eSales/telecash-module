<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Model;

use DOMDocument;
use DOMException;
use DOMNode;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashConstants;

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
     * @param DOMDocument $document
     *
     * @return DOMNode
     * @throws DOMException
     */
    public function getXML(DOMDocument $document): DOMNode
    {
        $xml = $document->createElement(TeleCashConstants::PREF_V1 . 'Payment');

        if (!empty($this->hostedDataId)) {
            $hostedDataId = $document->createElement(TeleCashConstants::PREF_V1 . 'HostedDataID');
            $hostedDataId->textContent = $this->hostedDataId;

            $xml->appendChild($hostedDataId);
        }

        if (!empty($this->amount)) {
            $amount                = $document->createElement(TeleCashConstants::PREF_V1 . 'ChargeTotal');
            $amount->textContent   = (string)$this->amount;
            $currency              = $document->createElement(TeleCashConstants::PREF_V1 . 'Currency');
            $currency->textContent = self::CURRENCY_EUR;

            $xml->appendChild($amount);
            $xml->appendChild($currency);
        }

        return $xml;
    }
}
