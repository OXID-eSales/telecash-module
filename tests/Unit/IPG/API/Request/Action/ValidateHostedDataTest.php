<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;

use OxidSolutionCatalysts\TeleCash\IPG\API\Model\Payment;
use Prophecy\Prophet;

/**
 * Test case for Request/Action/ValidateHostedData
 */
class ValidateHostedDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param Payment $payment
     *
     * @dataProvider dataProvider
     */
    public function testXMLGeneration($payment)
    {
        $prophet = new Prophet();
        $orderService  = $prophet->prophesize('OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService');

        $validate = new ValidateHostedData($orderService->reveal(), $payment);
        $document = $validate->getDocument();
        $document->appendChild($validate->getElement());

        $elementValidate = $document->getElementsByTagName('ns2:Validate');
        $this->assertEquals(1, $elementValidate->length, 'Expected element Validate not found');

        $children = [];
        /** @var \DOMNode $child */
        foreach ($elementValidate->item(0)->childNodes as $child) {
            $children[$child->nodeName] = $child->nodeValue;
        }

        $this->assertArrayHasKey('ns1:Payment', $children, 'Expected element Payment not found');
        //no need to further test Payment, as this is already covered in PaymentTest
    }

    /**
     * Provides some test values
     *
     * @return array
     */
    public static function dataProvider()
    {
        return [
            [new Payment('abc-def')]
        ];
    }
}
