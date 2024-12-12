<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\IPG\API\Model;

use DOMDocument;
use DOMException;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\BillingData;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashConstants;
use PHPUnit\Framework\TestCase;

class BillingDataTest extends TestCase
{
    private BillingData $billingData;
    private DOMDocument $document;

    protected function setUp(): void
    {
        $this->billingData = new BillingData('Test Name');
        $this->document = new DOMDocument();
    }

    /**
     * @throws DOMException
     */
    public function testConstructorSetsName(): void
    {
        $xml = $this->billingData->getXML($this->document);
        $nameElements = $xml->getElementsByTagName('ns1:Name');

        $this->assertCount(1, $nameElements);
        $this->assertEquals('Test Name', $nameElements->item(0)->textContent);
    }

    /**
     * @throws DOMException
     */
    public function testAllSettersAndXMLGeneration(): void
    {
        $this->billingData
            ->setBrowserIP('192.168.1.1')
            ->setBrowserScreenHeight('1080')
            ->setBrowserScreenWidth('1920')
            ->setFirstName('John')
            ->setMiddleName('Michael')
            ->setSurName('Doe')
            ->setCustomerID('CUST123')
            ->setPhone('+1234567890')
            ->setFax('+0987654321')
            ->setEmail('john.doe@example.com')
            ->setAddress1('Street 123')
            ->setAddress2('Apt 4B')
            ->setCity('Berlin')
            ->setState('Berlin')
            ->setZip('10115')
            ->setCountry('DE')
            ->setAccountOwnerType('Personal');

        $xml = $this->billingData->getXML($this->document);

        $expectedElements = [
            'BrowserIP' => '192.168.1.1',
            'BrowserScreenHeight' => '1080',
            'BrowserScreenWidth' => '1920',
            'Name' => 'Test Name',
            'CustomerID' => 'CUST123',
            'Firstname' => 'John',
            'Middlename' => 'Michael',
            'Surname' => 'Doe',
            'Phone' => '+1234567890',
            'Fax' => '+0987654321',
            'Email' => 'john.doe@example.com',
            'Address1' => 'Street 123',
            'Address2' => 'Apt 4B',
            'City' => 'Berlin',
            'State' => 'Berlin',
            'Zip' => '10115',
            'Country' => 'DE',
            'AccountOwnerType' => 'Personal'
        ];

        foreach ($expectedElements as $elementName => $expectedValue) {
            $element = $xml->getElementsByTagName('ns1:' . $elementName)->item(0);
            $this->assertNotNull($element, "Element $elementName should exist");
            $this->assertEquals($expectedValue, $element->textContent);
        }
    }

    /**
     * @throws DOMException
     */
    public function testOptionalFieldsAreNotIncludedWhenEmpty(): void
    {
        $this->billingData
            ->setFirstName('John')
            ->setEmail('john@example.com');

        $xml = $this->billingData->getXML($this->document);

        // These elements should exist
        $this->assertNotNull($xml->getElementsByTagName('ns1:Name')->item(0));
        $this->assertNotNull($xml->getElementsByTagName('ns1:Firstname')->item(0));
        $this->assertNotNull($xml->getElementsByTagName('ns1:Email')->item(0));

        // These elements should not exist
        $this->assertNull($xml->getElementsByTagName('ns1:Address1')->item(0));
        $this->assertNull($xml->getElementsByTagName('ns1:Phone')->item(0));
        $this->assertNull($xml->getElementsByTagName('ns1:City')->item(0));
        $this->assertNull($xml->getElementsByTagName('ns1:Country')->item(0));
    }

    public function testFluentInterface(): void
    {
        $returnValue = $this->billingData->setEmail('test@example.com');
        $this->assertInstanceOf(BillingData::class, $returnValue);
        $this->assertSame($this->billingData, $returnValue);
    }
}
