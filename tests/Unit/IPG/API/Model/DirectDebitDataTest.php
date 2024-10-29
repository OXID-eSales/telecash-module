<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Model;

class DirectDebitDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param string|null $bankCode
     * @param string|null $accountNumber
     *
     * @dataProvider dataProvider
     */
    public function testXMLDataCreation(string|null $iBan, string|null $bankCode, string|null $accountNumber)
    {
        $directDebitData = new DirectDebitData($iBan, $bankCode, $accountNumber);

        $document = new \DOMDocument('1.0', 'UTF-8');

        $directDebitData->setNamespaceShort('test');
        $xml = $directDebitData->getXML($document);
        $document->appendChild($xml);
        $elementCCData = $document->getElementsByTagName('test:DE_DirectDebitData');
        $this->assertEquals(1, $elementCCData->length, 'Expected "test" element DirectDebitData not found');

        $directDebitData->setNamespaceShort('ns2');
        $xml = $directDebitData->getXML($document);
        $this->assertEquals('ns2:DE_DirectDebitData', $xml->nodeName);

        if ($iBan !== null) {
            $iBanNode = $xml->getElementsByTagName('ns1:IBAN')->item(0);
            $this->assertEquals($iBan, $iBanNode->textContent);
        }
        if ($bankCode !== null && $accountNumber !== null) {
            $bankCodeNode = $xml->getElementsByTagName('ns1:BankCode')->item(0);
            $this->assertEquals($bankCode, $bankCodeNode->textContent);

            $accountNumberNode = $xml->getElementsByTagName('ns1:AccountNumber')->item(0);
            $this->assertEquals($accountNumber, $accountNumberNode->textContent);
        }
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
