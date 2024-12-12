<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\IPG\API\Model;

use DOMDocument;
use DOMException;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\CreditCardData;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\CreditCardItem;
use PHPUnit\Framework\TestCase;

/**
 * Test case for CreditCardItem
 *
 * @package Checkdomain\TeleCash\IPG\API\Model
 */
class CreditCardItemTest extends TestCase
{
    private DOMDocument $document;

    protected function setUp(): void
    {
        $this->document = new DOMDocument('1.0', 'UTF-8');
    }

    /**
     * @param CreditCardData $creditCardData
     * @param string|null $hostedDataId
     * @param string|null $function
     * @param string|null $declineHostedDataDuplicates
     *
     * @dataProvider dataProvider
     * @throws DOMException
     */
    public function testXMLGeneration(
        CreditCardData $creditCardData,
        ?string $hostedDataId,
        ?string $function,
        ?string $declineHostedDataDuplicates
    ): void {
        $item = new CreditCardItem($creditCardData, $hostedDataId, $function, $declineHostedDataDuplicates);
        $xml = $item->getXML($this->document);
        $this->document->appendChild($xml);

        // Test root element
        $elementDSItem = $this->document->getElementsByTagName('ns2:DataStorageItem');
        $this->assertEquals(
            1,
            $elementDSItem->length,
            'Expected element DataStorageItem not found'
        );

        // Get all child elements
        $children = [];
        foreach ($elementDSItem->item(0)->childNodes as $child) {
            $children[$child->nodeName] = $child->textContent;
        }

        // Test required elements
        $this->assertArrayHasKey(
            'ns2:HostedDataID',
            $children,
            'Expected element HostedDataId not found'
        );
        $this->assertEquals(
            $hostedDataId,
            $children['ns2:HostedDataID'],
            'Hosted data id did not match'
        );

        // Test optional elements
        if ($function !== null) {
            $this->assertArrayHasKey(
                'ns2:Function',
                $children,
                'Expected element Function not found'
            );
            $this->assertEquals(
                $function,
                $children['ns2:Function'],
                'Function did not match'
            );
        }

        if ($declineHostedDataDuplicates !== null) {
            $this->assertArrayHasKey(
                'ns2:DeclineHostedDataDuplicates',
                $children,
                'Expected element DeclineHostedDataDuplicates not found'
            );
            $this->assertEquals(
                $declineHostedDataDuplicates,
                $children['ns2:DeclineHostedDataDuplicates'],
                'DeclineHostedDataDuplicates did not match'
            );
        }

        // Test CreditCardData element
        $this->assertArrayHasKey(
            'ns2:CreditCardData',
            $children,
            'Expected element CreditCardData not found'
        );
    }

    /**
     * Provides some test values
     *
     * @return array
     */
    public static function dataProvider(): array
    {
        return [
            [new CreditCardData('12345678901234', '12', '20'), 'abc-def', null, null],
            [new CreditCardData('12345678901234', '12', '20'), 'abc-def', 'display', null],
            [new CreditCardData('12345678901234', '12', '20'), 'abc-def', 'display', 'true']
        ];
    }
}
