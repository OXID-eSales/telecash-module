<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action\RecurringPayment;

use OxidSolutionCatalysts\TeleCash\IPG\API\Model\Payment;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\RecurringPaymentInformation;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Test class for RecurringPayment Modify request action
 *
 * Validates the XML generation for modifying existing recurring payments
 * in the TeleCash system, including updates to payment and scheduling information.
 */
class ModifyTest extends TestCase
{
    /**
     * Tests the XML generation for the Modify request
     *
     * Verifies that:
     * 1. The RecurringPayment element exists
     * 2. Updated payment information is included
     * 3. Modified recurring payment details are properly formatted
     * 4. The function type is set to 'modify'
     * 5. The original order ID is preserved
     *
     * @param string $orderId The order ID to modify
     * @param Payment $payment Updated payment details
     * @param RecurringPaymentInformation $paymentInformation Modified recurring payment configuration
     * @dataProvider dataProvider
     * @throws Exception
     */
    public function testXMLGeneration(
        string $orderId,
        Payment $payment,
        RecurringPaymentInformation $paymentInformation
    ): void {
        $orderService  = $this->createMock(OrderService::class);

        $recurring = new Modify($orderService, $orderId, $payment, $paymentInformation);
        $document  = $recurring->getDocument();

        $document->appendChild($recurring->getElement());

        // Verify RecurringPayment element exists
        $elementRecurringPayment = $document->getElementsByTagName('ns2:RecurringPayment');
        $this->assertEquals(
            1,
            $elementRecurringPayment->length,
            'XML must contain exactly one RecurringPayment element'
        );

        // Extract child elements for verification
        $children = [];
        /** @var \DOMNode $child */
        foreach ($elementRecurringPayment->item(0)->childNodes as $child) {
            $children[$child->nodeName] = $child->nodeValue;
        }

        // Verify required elements
        $this->assertArrayHasKey(
            'ns2:RecurringPaymentInformation',
            $children,
            'Modify request must contain RecurringPaymentInformation'
        );

        $this->assertArrayHasKey(
            'ns1:Payment',
            $children,
            'Modify request must contain Payment information'
        );

        $this->assertArrayHasKey(
            'ns2:Function',
            $children,
            'Modify request must contain Function element'
        );

        $this->assertEquals(
            'modify',
            $children['ns2:Function'],
            'Function must be set to "modify"'
        );

        $this->assertArrayHasKey(
            'ns2:OrderId',
            $children,
            'Modify request must contain OrderId'
        );
    }

    /**
     * Provides test data for modify request testing
     *
     * Creates test cases with:
     * - Order ID to modify
     * - Updated payment information
     * - Modified recurring payment configuration
     *
     * @return array Array of test cases
     */
    public static function dataProvider(): array
    {
        return [
            [
                '8934htgien g34hgigh30gj50o',
                new Payment('abc-def', 2),
                new RecurringPaymentInformation(
                    new \DateTime(),
                    1,
                    1,
                    RecurringPaymentInformation::PERIOD_MONTH
                )
            ],
        ];
    }
}
