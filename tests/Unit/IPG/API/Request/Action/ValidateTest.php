<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;

use OxidSolutionCatalysts\TeleCash\IPG\API\Model\CreditCardData;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;

/**
 * Test class for Validate request action
 *
 * Validates the XML generation for credit card validation requests
 * in the TeleCash system, including various amounts and comment scenarios.
 */
class ValidateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests the XML generation for the Validate request
     *
     * Verifies that:
     * 1. The Validate element exists
     * 2. Credit card data is properly included
     * 3. Optional payment information is handled correctly
     * 4. Comments are included when provided
     * 5. Different amount scenarios are handled properly
     *
     * @param CreditCardData $ccData Credit card data to validate
     * @param float $amount Transaction amount
     * @param string|null $text Optional comment text
     * @dataProvider dataProvider
     */
    public function testXMLGeneration(CreditCardData $ccData, float $amount, ?string $text): void
    {
        // Create mock for order service
        $orderService = $this->createMock(OrderService::class);

        // Create and build the validate request
        $validate = new Validate($orderService, $ccData, $amount, $text);
        $document = $validate->getDocument();
        $document->appendChild($validate->getElement());

        // Verify Validate element exists
        $elementValidate = $document->getElementsByTagName('ns2:Validate');
        $this->assertEquals(
            1,
            $elementValidate->length,
            'XML must contain exactly one Validate element'
        );

        // Extract child elements for verification
        $children = [];
        /** @var \DOMNode $child */
        foreach ($elementValidate->item(0)->childNodes as $child) {
            $children[$child->nodeName] = $child->nodeValue;
        }

        // Verify CreditCardData presence
        $this->assertArrayHasKey(
            'ns2:CreditCardData',
            $children,
            'Validate request must contain CreditCardData'
        );

        // Check Payment element based on amount
        if ($amount !== 1.0) {
            $this->assertArrayHasKey(
                'ns1:Payment',
                $children,
                'Validate request must contain Payment for non-default amount'
            );

            // Verify payment amount
            $elementPayment = $document->getElementsByTagName('ns1:Payment');
            $paymentChildren = [];
            /** @var \DOMNode $child */
            foreach ($elementPayment->item(0)->childNodes as $child) {
                $paymentChildren[$child->nodeName] = $child->nodeValue;
            }

            $this->assertEquals(
                $amount,
                $paymentChildren['ns1:ChargeTotal'],
                'Payment amount must match provided value'
            );
        } else {
            $this->assertArrayNotHasKey(
                'ns1:Payment',
                $children,
                'Validate request should not contain Payment for default amount'
            );
        }

        // Check TransactionDetails based on text presence
        if ($text !== null) {
            $this->assertArrayHasKey(
                'ns2:TransactionDetails',
                $children,
                'Validate request must contain TransactionDetails when comment provided'
            );

            // Verify comment text
            $elementDetails = $document->getElementsByTagName('ns2:TransactionDetails');
            $detailsChildren = [];
            /** @var \DOMNode $child */
            foreach ($elementDetails->item(0)->childNodes as $child) {
                $detailsChildren[$child->nodeName] = $child->nodeValue;
            }

            $this->assertEquals(
                $text,
                $detailsChildren['ns1:Comments'],
                'Transaction comment must match provided text'
            );
        } else {
            $this->assertArrayNotHasKey(
                'ns2:TransactionDetails',
                $children,
                'Validate request should not contain TransactionDetails without comment'
            );
        }
    }

    /**
     * Provides test data for validation request testing
     *
     * Test cases include:
     * 1. Default amount without comment
     * 2. Custom amount without comment
     * 3. Default amount with comment
     * 4. Custom amount with comment
     *
     * @return array Array of test cases
     */
    public static function dataProvider(): array
    {
        return [
            [new CreditCardData('12345678901234', '01', '00'), 1.0, null],
            [new CreditCardData('12345678901234', '01', '00'), 3.0, null],
            [new CreditCardData('12345678901234', '01', '00'), 1.0, 'Testkommentar'],
            [new CreditCardData('12345678901234', '01', '00'), 3.0, 'Testkommentar'],
        ];
    }
}
