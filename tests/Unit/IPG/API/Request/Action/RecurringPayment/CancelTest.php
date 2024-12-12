<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action\RecurringPayment;

use DOMException;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Test class for RecurringPayment Cancel request action
 *
 * Verifies the XML generation for canceling recurring payments in the TeleCash system.
 * This test ensures proper formatting of the cancellation request XML structure.
 */
class CancelTest extends TestCase
{
    /**
     * Tests the XML generation for the Cancel request
     *
     * Verifies that:
     * 1. The RecurringPayment element is present
     * 2. Required elements exist with correct values
     * 3. Optional elements are not present
     * 4. The function type is set to 'cancel'
     *
     * @param string $orderId The order ID to be canceled
     * @throws DOMException
     * @throws Exception
     * @dataProvider dataProvider
     */
    public function testXMLGeneration(string $orderId): void
    {
        $orderService  = $this->createMock(OrderService::class);

        $recurring = new Cancel($orderService, $orderId);
        $document  = $recurring->getDocument();

        $document->appendChild($recurring->getElement());

        // Verify RecurringPayment element exists
        $elementRecurringPayment = $document->getElementsByTagName('ns2:RecurringPayment');
        $this->assertEquals(
            1,
            $elementRecurringPayment->length,
            'XML must contain exactly one RecurringPayment element'
        );

        // Extract all child elements for verification
        $children = [];
        /** @var \DOMNode $child */
        foreach ($elementRecurringPayment->item(0)->childNodes as $child) {
            $children[$child->nodeName] = $child->nodeValue;
        }

        // Verify required and optional elements
        $this->assertArrayNotHasKey(
            'ns2:RecurringPaymentInformation',
            $children,
            'Cancel request should not contain RecurringPaymentInformation'
        );

        $this->assertArrayNotHasKey(
            'ns1:Payment',
            $children,
            'Cancel request should not contain Payment information'
        );

        $this->assertArrayHasKey(
            'ns2:Function',
            $children,
            'Cancel request must contain Function element'
        );

        $this->assertEquals(
            'cancel',
            $children['ns2:Function'],
            'Function must be set to "cancel"'
        );

        $this->assertArrayHasKey(
            'ns2:OrderId',
            $children,
            'Cancel request must contain OrderId'
        );
    }

    /**
     * Provides test data for cancel request testing
     *
     * @return array Array of test cases with order IDs
     */
    public static function dataProvider(): array
    {
        return [
            ['8934htgien g34hgigh30gj50o'], // Sample order ID
        ];
    }
}
