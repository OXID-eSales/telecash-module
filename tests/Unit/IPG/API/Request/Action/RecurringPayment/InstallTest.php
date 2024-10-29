<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action\RecurringPayment;

use OxidSolutionCatalysts\TeleCash\IPG\API\Model\Payment;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\RecurringPaymentInformation;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;

/**
 * Test class for RecurringPayment Install request action
 *
 * Validates the XML generation for setting up new recurring payments
 * in the TeleCash system, including payment and recurring payment information.
 */
class InstallTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests the XML generation for the Install request
     *
     * Verifies that:
     * 1. The RecurringPayment element exists
     * 2. All required payment information is included
     * 3. Recurring payment details are properly formatted
     * 4. The function type is set to 'install'
     *
     * @param Payment $payment Payment details
     * @param RecurringPaymentInformation $paymentInformation Recurring payment configuration
     * @dataProvider dataProvider
     */
    public function testXMLGeneration(Payment $payment, RecurringPaymentInformation $paymentInformation): void
    {
        $orderService  = $this->createMock(OrderService::class);

        $recurring = new Install($orderService, $payment, $paymentInformation);
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
            'Install request must contain RecurringPaymentInformation'
        );

        $this->assertArrayHasKey(
            'ns1:Payment',
            $children,
            'Install request must contain Payment information'
        );

        $this->assertArrayHasKey(
            'ns2:Function',
            $children,
            'Install request must contain Function element'
        );

        $this->assertEquals(
            'install',
            $children['ns2:Function'],
            'Function must be set to "install"'
        );

        $this->assertArrayNotHasKey(
            'ns2:OrderId',
            $children,
            'Install request should not contain OrderId'
        );
    }

    /**
     * Provides test data for install request testing
     *
     * Creates test cases with:
     * - Payment information
     * - Recurring payment configuration
     *
     * @return array Array of test cases
     */
    public static function dataProvider(): array
    {
        return [
            [
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
