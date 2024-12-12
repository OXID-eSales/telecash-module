<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;

use DOMException;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\Payment;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Test class for ValidateHostedData request action
 *
 * Validates the XML generation for validating stored payment data
 * in the TeleCash system.
 */
class ValidateHostedDataTest extends TestCase
{
    /**
     * Tests the XML generation for the ValidateHostedData request
     *
     * Verifies that:
     * 1. The Validate element exists
     * 2. The Payment information is properly included
     * 3. The XML structure meets the required format
     *
     * @param Payment $payment The payment information to validate
     * @throws DOMException
     * @throws Exception
     * @dataProvider dataProvider
     */
    public function testXMLGeneration(Payment $payment): void
    {
        // Create mock for order service
        $orderService = $this->createMock(OrderService::class);

        // Create and build the validate request
        $validate = new ValidateHostedData($orderService, $payment);
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

        // Verify Payment element presence
        $this->assertArrayHasKey(
            'ns1:Payment',
            $children,
            'Validate request must contain Payment element'
        );
        // Detailed Payment testing is covered in PaymentTest
    }

    /**
     * Provides test data for validation request testing
     *
     * @return array Array of test cases with Payment objects
     */
    public static function dataProvider(): array
    {
        return [
            [new Payment('abc-def')] // Basic payment test case
        ];
    }
}
