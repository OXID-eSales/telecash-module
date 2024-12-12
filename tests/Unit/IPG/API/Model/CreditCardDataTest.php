<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\IPG\API\Model;

use DOMException;
use DOMNode;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\CreditCardData;
use PHPUnit\Framework\TestCase;

/**
 * Test case for CreditCardData
 *
 * @package Checkdomain\TeleCash\IPG\API\Model
 */
class CreditCardDataTest extends TestCase
{
    private \DOMDocument $document;

    protected function setUp(): void
    {
        $this->document = new \DOMDocument('1.0', 'UTF-8');
    }

    /**
     * @param string|null $ccNumber
     * @param string|null $validMonth
     * @param string|null $validYear
     *
     * @dataProvider dataProvider
     * @throws DOMException
     */
    public function testXMLGeneration(?string $ccNumber, ?string $validMonth, ?string $validYear): void
    {
        $ccData = new CreditCardData($ccNumber, $validMonth, $validYear);

        // Test with custom namespace
        $ccData->setNamespaceShort('test');
        $xml = $ccData->getXML($this->document);
        $this->document->appendChild($xml);

        $elementCCData = $this->document->getElementsByTagName('test:CreditCardData');
        $this->assertEquals(1, $elementCCData->length, 'Expected "test" element CreditCardData not found');

        // Test with ns2 namespace
        $ccData->setNamespaceShort('ns2');
        $xml = $ccData->getXML($this->document);
        $this->document->appendChild($xml);

        $elementCCData = $this->document->getElementsByTagName('ns2:CreditCardData');
        $this->assertEquals(1, $elementCCData->length, 'Expected element CreditCardData not found');

        // Get all child elements
        $children = [];
        foreach ($elementCCData->item(0)->childNodes as $child) {
            $children[$child->nodeName] = $child->textContent;
        }

        // Test card number element
        if ($ccNumber !== null) {
            $this->assertArrayHasKey('ns1:CardNumber', $children, 'Expected element CardNumber not found');
            $this->assertEquals($ccNumber, $children['ns1:CardNumber'], 'Card number did not match');
        } else {
            $this->assertArrayNotHasKey('ns1:CardNumber', $children, 'Unexpected element CardNumber was found');
        }

        // Test required elements
        $this->assertArrayHasKey('ns1:ExpMonth', $children, 'Expected element ExpMonth not found');
        $this->assertEquals($validMonth, $children['ns1:ExpMonth'], 'Valid month did not match');

        $this->assertArrayHasKey('ns1:ExpYear', $children, 'Expected element ExpYear not found');
        $this->assertEquals($validYear, $children['ns1:ExpYear'], 'Valid year did not match');
    }

    /**
     * Provides some test values
     *
     * @return array
     */
    public static function dataProvider(): array
    {
        return [
            ['12345678901234', '12', '20'],
            ['41111111111111', '10', '20'],
            [null, '12', '16']
        ];
    }
}
