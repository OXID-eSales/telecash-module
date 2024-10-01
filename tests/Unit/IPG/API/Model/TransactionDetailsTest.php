<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Model;

/**
 * Test case for Payment
 *
 * @package Checkdomain\TeleCash\IPG\API\Model
 */
class TransactionDetailsTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @param string|null $comments
     * @param string|null $invoiceNumber
     *
     * @dataProvider dataProvider
     */
    public function testXMLGeneration(string|null $comments, string|null $invoiceNumber): void
    {
        $ccData   = new TransactionDetails('ns2', $comments, $invoiceNumber);
        $document = new \DOMDocument('1.0', 'UTF-8');
        $xml      = $ccData->getXML($document);
        $document->appendChild($xml);

        $elementPayment = $document->getElementsByTagName('ns2:TransactionDetails');
        $this->assertEquals(1, $elementPayment->length, 'Expected element TransactionDetails not found');

        $children = [];
        /** @var \DOMNode $child */
        foreach ($elementPayment->item(0)->childNodes as $child) {
            $children[$child->nodeName] = $child->nodeValue;
        }

        $this->assertArrayHasKey('ns1:Comments', $children, 'Expected element Comments not found');
        $this->assertEquals($comments, $children['ns1:Comments'], 'Comments data id did not match');

        if ($invoiceNumber !== null) {
            $this->assertArrayHasKey('ns1:InvoiceNumber', $children, 'Expected element InvoiceNumber not found');
            $this->assertEquals($invoiceNumber, $children['ns1:InvoiceNumber'], 'InvoiceNumber did not match');
        } else {
            $this->assertArrayNotHasKey('ns1:InvoiceNumber', $children, 'Unexpected element InvoiceNumber was found');
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
            ['Testkommentar', null],
            ['Testkommentar', '1234-TestTestTest']
        ];
    }
}
