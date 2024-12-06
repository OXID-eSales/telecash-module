<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Service;

use DOMDocument;
use DOMNode;
use Exception;
use OxidSolutionCatalysts\TeleCash\IPG\API\AbstractRequest;
use OxidSolutionCatalysts\TeleCash\IPG\API\Request\ActionRequest;
use OxidSolutionCatalysts\TeleCash\IPG\API\Request\OrderRequest;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Error;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashConstants;
use RuntimeException;

/**
 * Class OrderService
 */
class OrderService extends SoapClientCurl
{
    public const SOAP_ERROR_SERVER = 'SOAP-ENV:Server';
    public const SOAP_ERROR_CLIENT = 'SOAP-ENV:Client';

    public const SOAP_CLIENT_ERROR_MERCHANT   = 'MerchantException';
    public const SOAP_CLIENT_ERROR_PROCESSING = 'ProcessingException';

    private bool $debug;

    /**
     * @param array<int|string, mixed>  $curlOptions CURL config values
     * @param string $username    API user
     * @param string $password    API pass
     * @param bool   $debug       Flag, debug mode
     */
    public function __construct(array $curlOptions, string $username, string $password, bool $debug = false)
    {
        parent::__construct($curlOptions, $username, $password);

        $this->debug = $debug;
    }

    /**
     * @param DOMNode $element
     */
    public function dumpDOMElement(DOMNode $element): void
    {
        if ($element->ownerDocument !== null) {
            var_dump($element->ownerDocument->saveXML($element));
        }
    }

    public function dumpXML(string $source): void
    {
        $xml = new DOMDocument();
        $xml->loadXML($source);
        $xml->preserveWhiteSpace = false;
        $xml->formatOutput = true;
        var_dump($xml->saveXML());
    }

    /**
     * @param DOMDocument $responseDoc
     *
     * @return Error|null
     * @throws Exception
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    private function checkForSoapFault(DOMDocument $responseDoc): Error|null
    {
        return Error::createFromSoapFault($responseDoc);
    }

    /**
     * @param AbstractRequest $payload
     *
     * @return DOMDocument|Error
     *
     * @throws Exception
     */
    private function soapCall(AbstractRequest $payload): DOMDocument|Error
    {
        $request = $payload->getDocument();

        $envelope = $request->createElementNS('http://schemas.xmlsoap.org/soap/envelope/', 'SOAP-ENV:Envelope');
        $envelope->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:' . TeleCashConstants::V1,
            TeleCashConstants::NAMESPACE_V1
        );
        $envelope->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:' . TeleCashConstants::A1,
            TeleCashConstants::NAMESPACE_A1
        );
        $envelope->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:' . TeleCashConstants::IPGAPI,
            TeleCashConstants::NAMESPACE_IPGAPI
        );

        $body = $request->createElement('SOAP-ENV:Body');
        $body->appendChild($payload->getElement());
        $envelope->appendChild($body);

        $request->appendChild($envelope);
        $xml = $request->saveXML();

        if ($this->debug) {
            $this->dumpXML((string)$xml);
        }

        $response = false;
        if ($xml) {
            $response = $this->doRequest($xml);
        }

        if ($this->debug) {
            $this->dumpXML((string)$response);
        }

        if ($response === false) {
            throw new RuntimeException($this->getErrorMessage());
        }

        if (empty($response)) {
            throw new RuntimeException('Empty API response received');
        }

        $responseDoc = new DOMDocument('1.0', 'UTF-8');
        $responseDoc->loadXML($response);

        $errorResponse = $this->checkForSoapFault($responseDoc);

        return $errorResponse ?? $responseDoc;
    }

    /**
     * @param ActionRequest $actionRequest
     *
     * @return DOMDocument|Error
     * @throws Exception
     */
    public function IPGApiAction(ActionRequest $actionRequest): DOMDocument|Error
    {
        return $this->soapCall($actionRequest);
    }


    /**
     * @param OrderRequest $orderRequest
     *
     * @return DOMDocument|Error
     * @throws Exception
     */
    public function IPGApiOrder(OrderRequest $orderRequest): DOMDocument|Error
    {
        return $this->soapCall($orderRequest);
    }
}
