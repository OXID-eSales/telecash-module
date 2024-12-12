<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\IPG\API\Model;

use DOMDocument;
use DOMException;
use DOMNode;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\TransactionDetails;
use PHPUnit\Framework\TestCase;

/**
 * Test case for Payment
 *
 * @package Checkdomain\TeleCash\IPG\API\Model
 */
class TransactionDetailsTest extends TestCase
{
    private DOMDocument $document;

    protected function setUp(): void
    {
        $this->document = new DOMDocument('1.0', 'UTF-8');
    }

    /**
     * @param string|null $comments
     * @param string|null $invoiceNumber
     *
     * @dataProvider dataProvider
     * @throws DOMException
     */
    public function testXMLGeneration(?string $comments, ?string $invoiceNumber): void
    {
        $transactionDetails = new TransactionDetails('ns2', $comments, $invoiceNumber);
        $xml = $transactionDetails->getXML($this->document);
        $this->document->appendChild($xml);

        // Test root element
        $elementDetails = $this->document->getElementsByTagName('ns2:TransactionDetails');
        $this->assertEquals(
            1,
            $elementDetails->length,
            'Expected element TransactionDetails not found'
        );

        // Get all child elements
        $children = [];
        foreach ($elementDetails->item(0)->childNodes as $child) {
            $children[$child->nodeName] = $child->textContent;
        }

        // Test required Comments element
        $this->assertArrayHasKey(
            'ns1:Comments',
            $children,
            'Expected element Comments not found'
        );
        $this->assertEquals(
            $comments,
            $children['ns1:Comments'],
            'Comments did not match'
        );

        // Test optional InvoiceNumber element
        if ($invoiceNumber !== null) {
            $this->assertArrayHasKey(
                'ns1:InvoiceNumber',
                $children,
                'Expected element InvoiceNumber not found'
            );
            $this->assertEquals(
                $invoiceNumber,
                $children['ns1:InvoiceNumber'],
                'InvoiceNumber did not match'
            );
        } else {
            $this->assertArrayNotHasKey(
                'ns1:InvoiceNumber',
                $children,
                'Unexpected element InvoiceNumber was found'
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
            ['Testkommentar', null],
            ['Testkommentar', '1234-TestTestTest']
        ];
    }
}
