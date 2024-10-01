<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Model;

/**
 * Test case for Payment
 *
 * @package Checkdomain\TeleCash\IPG\API\Model
 */
class PaymentTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @param string|null $hostedDataId
     * @param float|null  $amount
     *
     * @dataProvider dataProvider
     */
    public function testXMLGeneration(string|null $hostedDataId, float|null $amount)
    {
        $ccData   = new Payment($hostedDataId, $amount);
        $document = new \DOMDocument('1.0', 'UTF-8');
        $xml      = $ccData->getXML($document);
        $document->appendChild($xml);

        $elementPayment = $document->getElementsByTagName('ns1:Payment');
        $this->assertEquals(1, $elementPayment->length, 'Expected element Payment not found');

        $children = [];
        /** @var \DOMNode $child */
        foreach ($elementPayment->item(0)->childNodes as $child) {
            $children[$child->nodeName] = $child->nodeValue;
        }

        if ($hostedDataId !== null) {
            $this->assertArrayHasKey('ns1:HostedDataID', $children, 'Expected element HostedDataID not found');
            $this->assertEquals($hostedDataId, $children['ns1:HostedDataID'], 'Hosted data id did not match');
        } else {
            $this->assertArrayNotHasKey('ns1:HostedDataID', $children, 'Unexpected element HostedDataID was found');
        }

        if ($amount !== null) {
            $this->assertArrayHasKey('ns1:ChargeTotal', $children, 'Expected element ChargeTotal not found');
            $this->assertEquals($amount, $children['ns1:ChargeTotal'], 'Charge total did not match');
            $this->assertArrayHasKey('ns1:Currency', $children, 'Expected element Currency not found');
            $this->assertEquals('978', $children['ns1:Currency'], 'Currency did not match');
        } else {
            $this->assertArrayNotHasKey('ns1:ChargeTotal', $children, 'Unexpected element ChargeTotal was found');
            $this->assertArrayNotHasKey('ns1:Currency', $children, 'Unexpected element Currency was found');
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
