<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Model;

use OxidSolutionCatalysts\TeleCash\IPG\API\Traits\PropertyTrait;
use OxidSolutionCatalysts\TeleCash\IPG\API\Exception\PropertyNotExistsException;

/**
 * @property string $browserIP
 * @property string $browserScreenHeight
 * @property string $browserScreenWidth
 * @property string $customerID
 * @property string $name
 * @property string $firstName
 * @property string $middleName
 * @property string $surName
 * @property string $phone
 * @property string $fax
 * @property string $email
 * @property string $address1
 * @property string $address2
 * @property string $city
 * @property string $state
 * @property string $zip
 * @property string $country
 * @property string $accountOwnerType
 *
 */
class BillingData implements ElementInterface
{
    use PropertyTrait;

    /** @var array<string, string> $property2Xml */
    private array $property2Xml = [
        'browserIP' => 'ns1:BrowserIP',
        'browserScreenHeight' => 'ns1:BrowserScreenHeight',
        'browserScreenWidth' => 'ns1:BrowserScreenWidth',
        'customerID' => 'ns1:CustomerID',
        'name' => 'ns1:Name',
        'firstName' => 'ns1:Firstname',
        'middleName' => 'ns1:Middlename',
        'surName' => 'ns1:Surname',
        'phone' => 'ns1:Phone',
        'fax' => 'ns1:Fax',
        'email' => 'ns1:Email',
        'address1' => 'ns1:Address1',
        'address2' => 'ns1:Address2',
        'city' => 'ns1:City',
        'state' => 'ns1:State',
        'zip' => 'ns1:Zip',
        'country' => 'ns1:Country',
        'accountOwnerType' => 'ns1:AccountOwnerType',
    ];

    public function __construct(string $name)
    {
        /** @var array<string, mixed> $classProperties */
        $classProperties = [];
        foreach (array_keys($this->property2Xml) as $property) {
            $classProperties[$property] = '';
        }

        $this->initProperties($classProperties);
        $this->name = $name;
    }

    private function getXmlName(string $property): string
    {
        return $this->property2Xml[$property];
    }

    private function addElementForClassProperty(\DOMDocument $document, string $property, \DOMElement $appendTo): void
    {
        if (!empty($this->$property)) {
            $element = $this->createElement(
                $document,
                $this->getXmlName($property),
                $this->$property
            );
            $appendTo->appendChild($element);
        }
    }

    /**
     * @inheritDoc
     */
    public function getXML(\DOMDocument $document): mixed
    {
        $xml = $document->createElement('ns1:Billing');

        foreach ($this->property2Xml as $property => $xmlName) {
            $this->addElementForClassProperty($document, $property, $xml);
        }

        return $xml;
    }

    private function createElement(\DOMDocument $document, string $elementName, string|null $elementValue): \DOMElement
    {
        $item = $document->createElement($elementName);
        $item->textContent = (string)$elementValue;
        return $item;
    }
}
