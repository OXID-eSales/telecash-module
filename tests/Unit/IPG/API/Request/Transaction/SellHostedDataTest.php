<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;

use OxidSolutionCatalysts\TeleCash\IPG\API\Model\Payment;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\TransactionDetails;
use OxidSolutionCatalysts\TeleCash\IPG\API\Request\Transaction\SellHostedData;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;

/**
 * Test class for SellHostedData transaction request
 *
 * Validates the XML generation for selling transactions using stored payment data
 * in the TeleCash system. Tests both simple sales and sales with additional
 * transaction details.
 */
class SellHostedDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests the XML generation for the SellHostedData request
     *
     * Verifies that:
     * 1. The credit card transaction type is properly set
     * 2. Payment information is correctly included
     * 3. Transaction details are handled appropriately (included or omitted)
     * 4. The overall XML structure meets the required format
     *
     * @param Payment|null $payment Payment information or null
     * @param TransactionDetails|null $transactionDetails Optional transaction details
     * @dataProvider dataProvider
     */
    public function testXMLGeneration(?Payment $payment, ?TransactionDetails $transactionDetails): void
    {
        // Create mock for order service
        $orderService = $this->createMock(OrderService::class);

        // Create and build the sell hosted data request
        $sellHosted = new SellHostedData($orderService, $payment, $transactionDetails);
        $document = $sellHosted->getDocument();
        $document->appendChild($sellHosted->getElement());

        // Verify CreditCardTxType element exists
        $elementCCType = $document->getElementsByTagName('ns1:CreditCardTxType');
        $this->assertEquals(
            1,
            $elementCCType->length,
            'XML must contain exactly one CreditCardTxType element'
        );

        // Extract CreditCardTxType child elements
        $children = [];
        /** @var \DOMNode $child */
        foreach ($elementCCType->item(0)->childNodes as $child) {
            $children[$child->nodeName] = $child->nodeValue;
        }

        // Verify transaction type
        $this->assertArrayHasKey(
            'ns1:Type',
            $children,
            'CreditCardTxType must contain Type element'
        );
        $this->assertEquals(
            'sale',
            $children['ns1:Type'],
            'Transaction type must be set to "sale"'
        );

        // Verify Payment element
        $elementPayment = $document->getElementsByTagName('ns1:Payment');
        $this->assertEquals(
            1,
            $elementPayment->length,
            'XML must contain exactly one Payment element'
        );

        // Verify TransactionDetails handling
        $elementDetails = $document->getElementsByTagName('ns2:TransactionDetails');
        if ($transactionDetails !== null) {
            $this->assertEquals(
                1,
                $elementDetails->length,
                'XML must contain TransactionDetails when provided'
            );
        } else {
            $this->assertEquals(
                0,
                $elementDetails->length,
                'XML must not contain TransactionDetails when not provided'
            );
        }
    }

    /**
     * Provides test data for sell hosted data request testing
     *
     * Test cases include:
     * 1. Basic sale with payment information only
     * 2. Sale with payment information and transaction details
     *
     * @return array Array of test cases combining Payment and TransactionDetails
     */
    public static function dataProvider(): array
    {
        return [
            'basic_sale' => [
                new Payment('abc-def'),
                null
            ],
            'sale_with_details' => [
                new Payment('abc-def'),
                new TransactionDetails('ns2', 'Testkommentar', '1234-TestTestTest')
            ],
        ];
    }
}
