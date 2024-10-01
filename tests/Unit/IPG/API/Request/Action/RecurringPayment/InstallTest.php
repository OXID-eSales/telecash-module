<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action\RecurringPayment;

use OxidSolutionCatalysts\TeleCash\IPG\API\Model\Payment;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\RecurringPaymentInformation;
use Prophecy\Prophet;

/**
 * Test case for Request/Action/RecurringPayment/Install
 */
class InstallTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @param Payment                     $payment
     * @param RecurringPaymentInformation $paymentInformation
     *
     * @dataProvider dataProvider
     */
    public function testXMLGeneration(Payment $payment, RecurringPaymentInformation $paymentInformation): void
    {
        $prophet = new Prophet();
        $orderService  = $prophet->prophesize('OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService');

        $recurring = new Install($orderService->reveal(), $payment, $paymentInformation);
        $document  = $recurring->getDocument();
        $document->appendChild($recurring->getElement());

        $elementRecurringPayment = $document->getElementsByTagName('ns2:RecurringPayment');
        $this->assertEquals(1, $elementRecurringPayment->length, 'Expected element RecurringPayment not found');

        $children = [];
        /** @var \DOMNode $child */
        foreach ($elementRecurringPayment->item(0)->childNodes as $child) {
            $children[$child->nodeName] = $child->nodeValue;
        }

        $this->assertArrayHasKey('ns2:RecurringPaymentInformation', $children, 'Expected element RecurringPaymentInformation not found');
        //no need to further test RecurringPaymentInformation, as this is already covered in RecurringPaymentInformationTest
        $this->assertArrayHasKey('ns1:Payment', $children, 'Expected element Payment not found');
        //no need to further test Payment, as this is already covered in PaymentTest
        $this->assertArrayHasKey('ns2:Function', $children, 'Expected element Function not found');
        $this->assertEquals('install', $children['ns2:Function'], 'Function did not match');
        $this->assertArrayNotHasKey('ns2:OrderId', $children, 'Unexpected element OrderId was found');
    }

    /**
     * Provides some test values
     *
     * @return array
     */
    public static function dataProvider(): array
    {
        return [
            [new Payment('abc-def', 2), new RecurringPaymentInformation(new \DateTime(), 1, 1, RecurringPaymentInformation::PERIOD_MONTH)],
        ];
    }
}
