<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Model\Tests;

use OxidSolutionCatalysts\TeleCash\IPG\API\Model\BillingData;
use PHPUnit\Framework\TestCase;

class BillingDataTest extends TestCase
{
    private BillingData $billingData;

    protected function setUp(): void
    {
        $this->billingData = new BillingData('Test Name');
    }

    public function testConstructorSetsName(): void
    {
        $document = new \DOMDocument();
        $xml = $this->billingData->getXML($document);
        $nameElement = $xml->getElementsByTagName('ns1:Name')->item(0);
        $this->assertEquals('Test Name', $nameElement->textContent);
    }

    public function testGettersAndSetters(): void
    {
        $this->billingData->firstName = 'John';
        $this->assertEquals('John', $this->billingData->firstName);

        $this->billingData->setMiddleName('Michael');
        $this->assertEquals('Michael', $this->billingData->getMiddleName());

        $this->assertNull($this->billingData->nonexistingProperty);
    }

    public function testNonexistentGetterThrowsException()
    {
        $this->expectException(\OxidSolutionCatalysts\TeleCash\IPG\API\Exception\PropertyNotExistsException::class);
        $this->billingData->getNonexistingProperty();
    }

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

        $document = new \DOMDocument();
        $xml = $this->billingData->getXML($document);

        // Test all XML elements
        $expectedElements = [
            'ns1:BrowserIP' => '192.168.1.1',
            'ns1:BrowserScreenHeight' => '1080',
            'ns1:BrowserScreenWidth' => '1920',
            'ns1:Name' => 'Test Name',
            'ns1:CustomerID' => 'CUST123',
            'ns1:Firstname' => 'John',
            'ns1:Middlename' => 'Michael',
            'ns1:Surname' => 'Doe',
            'ns1:Phone' => '+1234567890',
            'ns1:Fax' => '+0987654321',
            'ns1:Email' => 'john.doe@example.com',
            'ns1:Address1' => 'Street 123',
            'ns1:Address2' => 'Apt 4B',
            'ns1:City' => 'Berlin',
            'ns1:State' => 'Berlin',
            'ns1:Zip' => '10115',
            'ns1:Country' => 'DE',
            'ns1:AccountOwnerType' => 'Personal'
        ];

        foreach ($expectedElements as $elementName => $expectedValue) {
            $element = $xml->getElementsByTagName($elementName)->item(0);
            $this->assertNotNull($element, "Element $elementName should exist");
            $this->assertEquals(
                $expectedValue,
                $element->textContent,
                "Element $elementName should have correct value"
            );
        }
    }

    public function testOptionalFieldsAreNotIncludedWhenEmpty(): void
    {
        // Only set a few fields
        $this->billingData
            ->setFirstName('John')
            ->setEmail('john@example.com');

        $document = new \DOMDocument();
        $xml = $this->billingData->getXML($document);

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
