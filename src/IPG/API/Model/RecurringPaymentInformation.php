<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Model;

/**
 * Class RecurringPaymentInformation
 */
class RecurringPaymentInformation implements ElementInterface
{
    public const PERIOD_DAY   = 'day';
    public const PERIOD_WEEK  = 'week';
    public const PERIOD_MONTH = 'month';
    public const PERIOD_YEAR  = 'year';

    /** @var \DateTime|null $startDate */
    private \DateTime|null $startDate;
    /** @var int|null $installmentCount */
    private int|null $installmentCount;
    /** @var int|null $installmentFrequency */
    private int|null $installmentFrequency;
    /** @var string|null $installmentPeriod */
    private string|null $installmentPeriod;

    /**
     * @param \DateTime|null $startDate
     * @param int|null       $installmentCount
     * @param int|null       $installmentFrequency
     * @param string|null    $installmentPeriod
     */
    public function __construct(
        \DateTime|null $startDate,
        int|null $installmentCount,
        int|null $installmentFrequency,
        string|null $installmentPeriod
    ) {
        $this->startDate            = $startDate;
        $this->installmentCount     = $installmentCount;
        $this->installmentFrequency = $installmentFrequency;
        $this->installmentPeriod    = $installmentPeriod;
    }

    /**
     * @param \DOMDocument $document
     *
     * @return mixed
     */
    public function getXML(\DOMDocument $document): mixed
    {
        $xml = $document->createElement('ns2:RecurringPaymentInformation');

        if ($this->startDate !== null) {
            $startDate              = $document->createElement('ns2:RecurringStartDate');
            $startDate->textContent = $this->startDate->format('Ymd');
            $xml->appendChild($startDate);
        }

        if ($this->installmentCount !== null) {
            $installmentCount              = $document->createElement('ns2:InstallmentCount');
            $installmentCount->textContent = (string)$this->installmentCount;
            $xml->appendChild($installmentCount);
        }

        if ($this->installmentFrequency !== null) {
            $installmentFrequency              = $document->createElement('ns2:InstallmentFrequency');
            $installmentFrequency->textContent = (string)$this->installmentFrequency;
            $xml->appendChild($installmentFrequency);
        }

        if ($this->installmentFrequency !== null) {
            $installmentPeriod              = $document->createElement('ns2:InstallmentPeriod');
            $installmentPeriod->textContent = (string)$this->installmentPeriod;
            $xml->appendChild($installmentPeriod);
        }

        return $xml;
    }
}
