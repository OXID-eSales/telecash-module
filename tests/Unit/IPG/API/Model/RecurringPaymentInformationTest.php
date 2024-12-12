<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\IPG\API\Model;

use DateTime;
use DOMDocument;
use DOMException;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\RecurringPaymentInformation;
use PHPUnit\Framework\TestCase;

/**
 * Test case for RecurringPaymentInformation
 *
 * @package Checkdomain\TeleCash\IPG\API\Model
 */
class RecurringPaymentInformationTest extends TestCase
{
    private DOMDocument $document;

    protected function setUp(): void
    {
        $this->document = new DOMDocument('1.0', 'UTF-8');
    }

    /**
     * @param DateTime|null $startDate
     * @param int|null $installmentCount
     * @param int|null $installmentFrequency
     * @param string|null $installmentPeriod
     *
     * @dataProvider dataProvider
     * @throws DOMException
     */
    public function testXMLGeneration(
        ?DateTime $startDate,
        ?int $installmentCount,
        ?int $installmentFrequency,
        ?string $installmentPeriod
    ): void {
        $recurringInfo = new RecurringPaymentInformation(
            $startDate,
            $installmentCount,
            $installmentFrequency,
            $installmentPeriod
        );

        $xml = $recurringInfo->getXML($this->document);
        $this->document->appendChild($xml);

        // Test root element
        $elementInfo = $this->document->getElementsByTagName('ns2:RecurringPaymentInformation');
        $this->assertEquals(
            1,
            $elementInfo->length,
            'Expected element RecurringPaymentInformation not found'
        );

        // Get all child elements
        $children = [];
        foreach ($elementInfo->item(0)->childNodes as $child) {
            $children[$child->nodeName] = $child->textContent;
        }

        // Test start date (always required)
        $this->assertArrayHasKey(
            'ns2:RecurringStartDate',
            $children,
            'Expected element RecurringStartDate not found'
        );
        $this->assertEquals(
            $startDate->format('Ymd'),
            $children['ns2:RecurringStartDate'],
            'Start date did not match'
        );

        // Test optional elements
        if ($installmentCount !== null) {
            $this->assertArrayHasKey(
                'ns2:InstallmentCount',
                $children,
                'Expected element InstallmentCount not found'
            );
            $this->assertEquals(
                (string)$installmentCount,
                $children['ns2:InstallmentCount'],
                'Installment count did not match'
            );
        } else {
            $this->assertArrayNotHasKey(
                'ns2:InstallmentCount',
                $children,
                'Unexpected element InstallmentCount was found'
            );
        }

        if ($installmentFrequency !== null) {
            $this->assertArrayHasKey(
                'ns2:InstallmentFrequency',
                $children,
                'Expected element InstallmentFrequency not found'
            );
            $this->assertEquals(
                (string)$installmentFrequency,
                $children['ns2:InstallmentFrequency'],
                'Installment frequency did not match'
            );
        } else {
            $this->assertArrayNotHasKey(
                'ns2:InstallmentFrequency',
                $children,
                'Unexpected element InstallmentFrequency was found'
            );
        }

        if ($installmentPeriod !== null) {
            $this->assertArrayHasKey(
                'ns2:InstallmentPeriod',
                $children,
                'Expected element InstallmentPeriod not found'
            );
            $this->assertEquals(
                $installmentPeriod,
                $children['ns2:InstallmentPeriod'],
                'Installment period did not match'
            );
        } else {
            $this->assertArrayNotHasKey(
                'ns2:InstallmentPeriod',
                $children,
                'Unexpected element InstallmentPeriod was found'
            );
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
            [new DateTime(), null, null, null],
            [new DateTime(), 1, null, null],
            [new DateTime(), 1, 1, null],
            [new DateTime(), 1, 1, 'month']
        ];
    }
}
