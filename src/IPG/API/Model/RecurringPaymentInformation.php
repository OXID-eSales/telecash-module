<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Model;

use DOMDocument;
use DOMException;
use DOMNode;

/**
 * Class RecurringPaymentInformation
 */
class RecurringPaymentInformation implements ElementInterface
{
    public const PERIOD_DAY = 'day';
    public const PERIOD_WEEK = 'week';
    public const PERIOD_MONTH = 'month';
    public const PERIOD_YEAR = 'year';

    public function __construct(
        private readonly ?\DateTime $startDate,
        private readonly ?int $installmentCount,
        private readonly ?int $installmentFrequency,
        private readonly ?string $installmentPeriod
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
        $xml = $document->createElement('ns2:RecurringPaymentInformation');

        // Helper function to create elements
        $addElement = function (string $name, ?string $value) use ($document, $xml) {
            if ($value !== null) {
                $element = $document->createElement('ns2:' . $name);
                $element->textContent = $value;
                $xml->appendChild($element);
            }
        };

        // Add elements in order
        if ($this->startDate !== null) {
            $addElement('RecurringStartDate', $this->startDate->format('Ymd'));
        }

        if ($this->installmentCount !== null) {
            $addElement('InstallmentCount', (string)$this->installmentCount);
        }

        if ($this->installmentFrequency !== null) {
            $addElement('InstallmentFrequency', (string)$this->installmentFrequency);
        }

        if ($this->installmentPeriod !== null) {
            $addElement('InstallmentPeriod', $this->installmentPeriod);
        }

        return $xml;
    }
}
