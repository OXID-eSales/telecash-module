<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\IPG\API\Model;

use DOMDocument;
use DOMException;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\DirectDebitData;
use PHPUnit\Framework\TestCase;

class DirectDebitDataTest extends TestCase
{
    private DOMDocument $document;

    protected function setUp(): void
    {
        $this->document = new DOMDocument('1.0', 'UTF-8');
    }

    /**
     * @param string|null $iBan
     * @param string|null $bankCode
     * @param string|null $accountNumber
     *
     * @throws DOMException
     * @dataProvider dataProvider
     */
    public function testXMLDataCreation(?string $iBan, ?string $bankCode, ?string $accountNumber): void
    {
        $directDebitData = new DirectDebitData($iBan, $bankCode, $accountNumber);

        // Test with 'test' namespace
        $directDebitData->setNamespaceShort('test');
        $xml = $directDebitData->getXML($this->document);
        $this->document->appendChild($xml);

        $elementDDData = $this->document->getElementsByTagName('test:DE_DirectDebitData');
        $this->assertEquals(
            1,
            $elementDDData->length,
            'Expected "test" element DirectDebitData not found'
        );

        // Test with 'ns2' namespace
        $directDebitData->setNamespaceShort('ns2');
        $xml = $directDebitData->getXML($this->document);
        $this->assertEquals('ns2:DE_DirectDebitData', $xml->nodeName);

        // Test IBAN or BankCode/AccountNumber based on input
        if ($iBan !== null) {
            $iBanElement = $xml->getElementsByTagName('ns1:IBAN')->item(0);
            $this->assertNotNull($iBanElement, 'IBAN element should exist');
            $this->assertEquals($iBan, $iBanElement->textContent);

            // Verify bank code and account number don't exist
            $this->assertNull($xml->getElementsByTagName('ns1:BankCode')->item(0));
            $this->assertNull($xml->getElementsByTagName('ns1:AccountNumber')->item(0));
        }

        if ($bankCode !== null && $accountNumber !== null) {
            $bankCodeElement = $xml->getElementsByTagName('ns1:BankCode')->item(0);
            $this->assertNotNull($bankCodeElement, 'BankCode element should exist');
            $this->assertEquals($bankCode, $bankCodeElement->textContent);

            $accountNumberElement = $xml->getElementsByTagName('ns1:AccountNumber')->item(0);
            $this->assertNotNull($accountNumberElement, 'AccountNumber element should exist');
            $this->assertEquals($accountNumber, $accountNumberElement->textContent);

            // Verify IBAN doesn't exist
            $this->assertNull($xml->getElementsByTagName('ns1:IBAN')->item(0));
        }

        // Test required elements
        $mandateRefElement = $xml->getElementsByTagName('ns1:MandateReference')->item(0);
        $this->assertNotNull($mandateRefElement, 'MandateReference element should exist');
        $this->assertEquals('MandateReference', $mandateRefElement->textContent);

        $mandateTypeElement = $xml->getElementsByTagName('ns1:MandateType')->item(0);
        $this->assertNotNull($mandateTypeElement, 'MandateType element should exist');
        $this->assertEquals('SINGLE', $mandateTypeElement->textContent);
    }

    /**
     * Provides some test values
     *
     * @return array
     */
    public static function dataProvider(): array
    {
        return [
            [null, '50010060', '32121604'],
            ['DE1234567890', null, null],
        ];
    }
}
