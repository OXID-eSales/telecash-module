<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Model;

use DOMDocument;
use DOMElement;
use DOMException;
use DOMNode;

/**
 * Class BillingData
 *
 * @todo Consider to reduce the number of fields
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class BillingData implements ElementInterface
{
    private ?string $browserIP = null;
    private ?string $browserScreenHeight = null;
    private ?string $browserScreenWidth = null;
    private ?string $customerID = null;
    private ?string $name = null;
    private ?string $firstName = null;
    private ?string $middleName = null;
    private ?string $surName = null;
    private ?string $phone = null;
    private ?string $fax = null;
    private ?string $email = null;
    private ?string $address1 = null;
    private ?string $address2 = null;
    private ?string $city = null;
    private ?string $state = null;
    private ?string $zip = null;
    private ?string $country = null;
    private ?string $accountOwnerType = null;

    public function __construct(string $name)
    {
        $this->setName($name);
    }

    public function setName(string $value): self
    {
        $this->name = $value;
        return $this;
    }
    public function setFirstName(string $value): self
    {
        $this->firstName = $value;
        return $this;
    }
    public function setMiddleName(string $value): self
    {
        $this->middleName = $value;
        return $this;
    }
    public function setSurName(string $value): self
    {
        $this->surName = $value;
        return $this;
    }
    public function setCustomerID(string $value): self
    {
        $this->customerID = $value;
        return $this;
    }
    public function setPhone(string $value): self
    {
        $this->phone = $value;
        return $this;
    }
    public function setFax(string $value): self
    {
        $this->fax = $value;
        return $this;
    }
    public function setEmail(string $value): self
    {
        $this->email = $value;
        return $this;
    }
    public function setAddress1(string $value): self
    {
        $this->address1 = $value;
        return $this;
    }
    public function setAddress2(string $value): self
    {
        $this->address2 = $value;
        return $this;
    }
    public function setCity(string $value): self
    {
        $this->city = $value;
        return $this;
    }
    public function setState(string $value): self
    {
        $this->state = $value;
        return $this;
    }
    public function setZip(string $value): self
    {
        $this->zip = $value;
        return $this;
    }
    public function setCountry(string $value): self
    {
        $this->country = $value;
        return $this;
    }
    public function setBrowserIP(string $value): self
    {
        $this->browserIP = $value;
        return $this;
    }
    public function setBrowserScreenWidth(string $value): self
    {
        $this->browserScreenWidth = $value;
        return $this;
    }
    public function setBrowserScreenHeight(string $value): self
    {
        $this->browserScreenHeight = $value;
        return $this;
    }
    public function setAccountOwnerType(string $value): self
    {
        $this->accountOwnerType = $value;
        return $this;
    }

    /**
     * @inheritDoc
     * @throws DOMException
     */
    public function getXML(DOMDocument $document): DOMNode
    {
        // Create root element
        $billing = $document->createElement('ns1:Billing');

        // Helper function to add elements
        $addElement = function (string $name, ?string $value) use ($document, $billing) {
            if ($value !== null) {
                $element = $document->createElement('ns1:' . $name);
                $element->textContent = $value;
                $billing->appendChild($element);
            }
        };

        // Add elements in order
        $addElement('BrowserIP', $this->browserIP);
        $addElement('BrowserScreenHeight', $this->browserScreenHeight);
        $addElement('BrowserScreenWidth', $this->browserScreenWidth);
        $addElement('CustomerID', $this->customerID);
        $addElement('Name', $this->name);
        $addElement('Firstname', $this->firstName);
        $addElement('Middlename', $this->middleName);
        $addElement('Surname', $this->surName);
        $addElement('Phone', $this->phone);
        $addElement('Fax', $this->fax);
        $addElement('Email', $this->email);
        $addElement('Address1', $this->address1);
        $addElement('Address2', $this->address2);
        $addElement('City', $this->city);
        $addElement('State', $this->state);
        $addElement('Zip', $this->zip);
        $addElement('Country', $this->country);
        $addElement('AccountOwnerType', $this->accountOwnerType);

        return $billing;
    }
}
