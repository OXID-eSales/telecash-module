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
    public function testXMLDataCreation(string|null $bankCode, string|null $accountNumber)
    {
        $directDebitData = new DirectDebitData($bankCode, $accountNumber);

        $document = new \DOMDocument('1.0', 'UTF-8');
        $xml = $directDebitData->getXML($document);

        $this->assertEquals('ns2:DE_DirectDebitData', $xml->nodeName);

        $bankCodeNode = $xml->getElementsByTagName('ns3:BankCode')->item(0);
        $this->assertEquals($bankCode, $bankCodeNode->textContent);

        $accountNumberNode = $xml->getElementsByTagName('ns3:AccountNumber')->item(0);
        $this->assertEquals($accountNumber, $accountNumberNode->textContent);
    }

    /**
     * Provides some test values
     *
     * @return array
     */
    public static function dataProvider(): array
    {
        return [
            ['50010060', '32121604'],
            ['123456789', null],
            [null, '123456789'],
            [null, null]
        ];
    }
}
