<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action\RecurringPayment;

use Prophecy\Prophet;

/**
 * Test case for Request/Action/RecurringPayment/Cancel
 */
class CancelTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @param string $orderId
     *
     * @dataProvider dataProvider
     */
    public function testXMLGeneration(string $orderId): void
    {
        $prophet = new Prophet();
        $orderService  = $prophet->prophesize('OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService');

        $recurring = new Cancel($orderService->reveal(), $orderId);
        $document  = $recurring->getDocument();
        $document->appendChild($recurring->getElement());

        $elementRecurringPayment = $document->getElementsByTagName('ns2:RecurringPayment');
        $this->assertEquals(1, $elementRecurringPayment->length, 'Expected element RecurringPayment not found');

        $children = [];
        /** @var \DOMNode $child */
        foreach ($elementRecurringPayment->item(0)->childNodes as $child) {
            $children[$child->nodeName] = $child->nodeValue;
        }

        $this->assertArrayNotHasKey('ns2:RecurringPaymentInformation', $children, 'Unexpected element RecurringPaymentInformation was found');
        //no need to further test RecurringPaymentInformation, as this is already covered in RecurringPaymentInformationTest
        $this->assertArrayNotHasKey('ns1:Payment', $children, 'Unexpected element Payment was found');
        //no need to further test Payment, as this is already covered in PaymentTest
        $this->assertArrayHasKey('ns2:Function', $children, 'Expected element Function not found');
        $this->assertEquals('cancel', $children['ns2:Function'], 'Function did not match');
        $this->assertArrayHasKey('ns2:OrderId', $children, 'Expected element OrderId not found');

    }

    /**
     * Provides some test values
     *
     * @return array
     */
    public static function dataProvider(): array
    {
        return [
            ['8934htgien g34hgigh30gj50o'],
        ];
    }
}
