<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Model;

/**
 * Class BillingData
 *
 * @todo Consider to reduce the number of fields, CyclomaticComplexity and NPathComplexity
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
class BillingData implements ElementInterface
{
    private string|null $browserIP;
    private string|null $browserScreenHeight;
    private string|null $browserScreenWidth;
    private string|null $customerID;
    private string|null $name;
    private string|null $firstName;
    private string|null $middleName;
    private string|null $surName;
    private string|null $phone;
    private string|null $fax;
    private string|null $email;
    private string|null $address1;
    private string|null $address2;
    private string|null $city;
    private string|null $state;
    private string|null $zip;
    private string|null $country;
    private string|null $accountOwnerType;

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
     * @todo Consider to reduce the CyclomaticComplexity and NPathComplexity
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getXML(\DOMDocument $document): mixed
    {
        $xml = $document->createElement('ns1:Billing');

        if (!empty($this->browserIP)) {
            $xml->appendChild(
                $this->createElement($document, 'ns1:BrowserIP', $this->browserIP)
            );
        }
        if (!empty($this->browserScreenHeight)) {
            $xml->appendChild(
                $this->createElement($document, 'ns1:BrowserScreenHeight', $this->browserScreenHeight)
            );
        }
        if (!empty($this->browserScreenWidth)) {
            $xml->appendChild(
                $this->createElement($document, 'ns1:BrowserScreenWidth', $this->browserScreenWidth)
            );
        }
        if (!empty($this->customerID)) {
            $xml->appendChild(
                $this->createElement($document, 'ns1:CustomerID', $this->customerID)
            );
        }
        if (!empty($this->name)) {
            $xml->appendChild(
                $this->createElement($document, 'ns1:Name', $this->name)
            );
        }
        if (!empty($this->firstName)) {
            $xml->appendChild(
                $this->createElement($document, 'ns1:Firstname', $this->firstName)
            );
        }
        if (!empty($this->middleName)) {
            $xml->appendChild(
                $this->createElement($document, 'ns1:Middlename', $this->middleName)
            );
        }
        if (!empty($this->surName)) {
            $xml->appendChild(
                $this->createElement($document, 'ns1:Surname', $this->surName)
            );
        }
        if (!empty($this->phone)) {
            $xml->appendChild(
                $this->createElement($document, 'ns1:Phone', $this->phone)
            );
        }
        if (!empty($this->fax)) {
            $xml->appendChild(
                $this->createElement($document, 'ns1:Fax', $this->fax)
            );
        }
        if (!empty($this->email)) {
            $xml->appendChild(
                $this->createElement($document, 'ns1:Email', $this->email)
            );
        }
        if (!empty($this->address1)) {
            $xml->appendChild(
                $this->createElement($document, 'ns1:Address1', $this->address1)
            );
        }
        if (!empty($this->address2)) {
            $xml->appendChild(
                $this->createElement($document, 'ns1:Address2', $this->address2)
            );
        }
        if (!empty($this->city)) {
            $xml->appendChild(
                $this->createElement($document, 'ns1:City', $this->city)
            );
        }
        if (!empty($this->state)) {
            $xml->appendChild(
                $this->createElement($document, 'ns1:State', $this->state)
            );
        }
        if (!empty($this->zip)) {
            $xml->appendChild(
                $this->createElement($document, 'ns1:Zip', $this->zip)
            );
        }
        if (!empty($this->country)) {
            $xml->appendChild(
                $this->createElement($document, 'ns1:Country', $this->country)
            );
        }
        if (!empty($this->accountOwnerType)) {
            $xml->appendChild(
                $this->createElement($document, 'ns1:AccountOwnerType', $this->accountOwnerType)
            );
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
