<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\CreditCardData;
use Prophecy\Prophet;

/**
 * Test case for Request/Action/Validate
 */
class ValidateTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @param CreditCardData $ccData
     * @param float          $amount
     * @param string         $text
     *
     * @dataProvider dataProvider
     */
    public function testXMLGeneration($ccData, $amount, $text)
    {
        $prophet = new Prophet();
        $orderService  = $prophet->prophesize('OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService');

        $validate = new Validate($orderService->reveal(), $ccData, $amount, $text);
        $document = $validate->getDocument();
        $document->appendChild($validate->getElement());

        $elementValidate = $document->getElementsByTagName('ns2:Validate');
        $this->assertEquals(1, $elementValidate->length, 'Expected element Validate not found');

        $children = [];
        /** @var \DOMNode $child */
        foreach ($elementValidate->item(0)->childNodes as $child) {
            $children[$child->nodeName] = $child->nodeValue;
        }

        $this->assertArrayHasKey('ns2:CreditCardData', $children, 'Expected element CreditCardData not found');

        if ($amount !== 1.0) {
            $this->assertArrayHasKey('ns1:Payment', $children, 'Expected element Payment not found');

            $elementPayment  = $document->getElementsByTagName('ns1:Payment');
            $paymentChildren = [];
            /** @var \DOMNode $child */
            foreach ($elementPayment->item(0)->childNodes as $child) {
                $paymentChildren[$child->nodeName] = $child->nodeValue;
            }

            $this->assertEquals($amount, $paymentChildren['ns1:ChargeTotal'], 'Amount did not match');
        } else {
            $this->assertArrayNotHasKey('ns1:Payment', $children, 'Unexpected element Payment was found');
        }

        if ($text !== null) {
            $this->assertArrayHasKey('ns2:TransactionDetails', $children, 'Expected element TransactionDetails not found');

            $elementDetails  = $document->getElementsByTagName('ns2:TransactionDetails');
            $detailsChildren = [];
            /** @var \DOMNode $child */
            foreach ($elementDetails->item(0)->childNodes as $child) {
                $detailsChildren[$child->nodeName] = $child->nodeValue;
            }
            $this->assertEquals($text, $detailsChildren['ns1:Comments'], 'Comments did not match');
        } else {
            $this->assertArrayNotHasKey('ns2:TransactionDetails', $children, 'Unexpected element TransactionDetails was found');
        }
    }

    /**
     * Provides some test values
     *
     * @return array
     */
    public static function dataProvider()
    {
        return [
            [new CreditCardData('12345678901234', '01', '00'), 1.0, null],
            [new CreditCardData('12345678901234', '01', '00'), 3.0, null],
            [new CreditCardData('12345678901234', '01', '00'), 1.0, 'Testkommentar'],
            [new CreditCardData('12345678901234', '01', '00'), 3.0, 'Testkommentar'],
        ];
    }
}
