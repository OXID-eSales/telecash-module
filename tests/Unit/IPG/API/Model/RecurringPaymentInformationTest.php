<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Model;

/**
 * Test case for RecurringPaymentInformation
 *
 * @package Checkdomain\TeleCash\IPG\API\Model
 */
class RecurringPaymentInformationTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @param \DateTime|null $startDate
     * @param int|null       $installmentCount
     * @param int|null       $installmentFrequency
     * @param string|null    $installmentPeriod
     *
     * @dataProvider dataProvider
     */
    public function testXMLGeneration(\DateTime|null $startDate, int|null $installmentCount, int|null $installmentFrequency, string|null $installmentPeriod)
    {
        $ccData   = new RecurringPaymentInformation($startDate, $installmentCount, $installmentFrequency, $installmentPeriod);
        $document = new \DOMDocument('1.0', 'UTF-8');
        $xml      = $ccData->getXML($document);
        $document->appendChild($xml);

        $elementInfo = $document->getElementsByTagName('ns2:RecurringPaymentInformation');
        $this->assertEquals(1, $elementInfo->length, 'Expected element RecurringPaymentInformation not found');

        $children = [];
        /** @var \DOMNode $child */
        foreach ($elementInfo->item(0)->childNodes as $child) {
            $children[$child->nodeName] = $child->nodeValue;
        }

        $this->assertArrayHasKey('ns2:RecurringStartDate', $children, 'Expected element RecurringStartDate not found');
        $this->assertEquals($startDate->format('Ymd'), $children['ns2:RecurringStartDate'], 'Start date did not match');

        if ($installmentCount !== null) {
            $this->assertArrayHasKey('ns2:InstallmentCount', $children, 'Expected element InstallmentCount not found');
            $this->assertEquals($installmentCount, $children['ns2:InstallmentCount'], 'Installment count did not match');
        }

        if ($installmentFrequency !== null) {
            $this->assertArrayHasKey('ns2:InstallmentFrequency', $children, 'Expected element InstallmentFrequency not found');
            $this->assertEquals($installmentFrequency, $children['ns2:InstallmentFrequency'], 'Installment frequency did not match');
        }

        if ($installmentPeriod !== null) {
            $this->assertArrayHasKey('ns2:InstallmentPeriod', $children, 'Expected element InstallmentPeriod not found');
            $this->assertEquals($installmentPeriod, $children['ns2:InstallmentPeriod'], 'Installment period did not match');
        }
    }

    /**
     * Provides some test values
     *
     * @return array
     */
    public static function dataProvider(): array
    {
        return [
            [new \DateTime(), null, null, null],
            [new \DateTime(), 1, null, null],
            [new \DateTime(), 1, 1, null],
            [new \DateTime(), 1, 1, 'month']
        ];
    }
}
