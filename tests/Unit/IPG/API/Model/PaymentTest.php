<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\IPG\API\Model;

use DOMDocument;
use DOMException;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\Payment;
use PHPUnit\Framework\TestCase;

/**
 * Test case for Payment
 *
 * @package Checkdomain\TeleCash\IPG\API\Model
 */
class PaymentTest extends TestCase
{
    private DOMDocument $document;

    protected function setUp(): void
    {
        $this->document = new DOMDocument('1.0', 'UTF-8');
    }

    /**
     * @param string|null $hostedDataId
     * @param float|null $amount
     *
     * @dataProvider dataProvider
     * @throws DOMException
     */
    public function testXMLGeneration(?string $hostedDataId, ?float $amount): void
    {
        $payment = new Payment($hostedDataId, $amount);
        $xml = $payment->getXML($this->document);
        $this->document->appendChild($xml);

        // Test root element
        $elementPayment = $this->document->getElementsByTagName('ns1:Payment');
        $this->assertEquals(
            1,
            $elementPayment->length,
            'Expected element Payment not found'
        );

        // Get all child elements
        $children = [];
        foreach ($elementPayment->item(0)->childNodes as $child) {
            $children[$child->nodeName] = $child->textContent;
        }

        // Test HostedDataID element
        if ($hostedDataId !== null) {
            $this->assertArrayHasKey(
                'ns1:HostedDataID',
                $children,
                'Expected element HostedDataID not found'
            );
            $this->assertEquals(
                $hostedDataId,
                $children['ns1:HostedDataID'],
                'Hosted data id did not match'
            );
        } else {
            $this->assertArrayNotHasKey(
                'ns1:HostedDataID',
                $children,
                'Unexpected element HostedDataID was found'
            );
        }

        // Test amount-related elements
        if ($amount !== null) {
            $this->assertArrayHasKey(
                'ns1:ChargeTotal',
                $children,
                'Expected element ChargeTotal not found'
            );
            $this->assertEquals(
                $amount,
                (float)$children['ns1:ChargeTotal'],
                'Charge total did not match'
            );
            $this->assertArrayHasKey(
                'ns1:Currency',
                $children,
                'Expected element Currency not found'
            );
            $this->assertEquals(
                '978',
                $children['ns1:Currency'],
                'Currency did not match'
            );
        } else {
            $this->assertArrayNotHasKey(
                'ns1:ChargeTotal',
                $children,
                'Unexpected element ChargeTotal was found'
            );
            $this->assertArrayNotHasKey(
                'ns1:Currency',
                $children,
                'Unexpected element Currency was found'
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
            ['abc-def', null],
            ['abc-def', 1.23],
            [null, 1.23]
        ];
    }
}
