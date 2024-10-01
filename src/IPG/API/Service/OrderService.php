<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Service;

use OxidSolutionCatalysts\TeleCash\IPG\API\AbstractRequest;
use OxidSolutionCatalysts\TeleCash\IPG\API\Request\ActionRequest;
use OxidSolutionCatalysts\TeleCash\IPG\API\Request\OrderRequest;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Error;

/**
 * Class OrderService
 */
class OrderService extends SoapClientCurl
{

    const NAMESPACE_N1   = 'http://ipg-online.com/ipgapi/schemas/v1';
    const NAMESPACE_N2   = 'http://ipg-online.com/ipgapi/schemas/a1';
    const NAMESPACE_N3   = 'http://ipg-online.com/ipgapi/schemas/ipgapi';
    const NAMESPACE_SOAP = 'http://schemas.xmlsoap.org/soap/envelope/';

    const SOAP_ERROR_SERVER = 'SOAP-ENV:Server';
    const SOAP_ERROR_CLIENT = 'SOAP-ENV:Client';

    const SOAP_CLIENT_ERROR_MERCHANT   = 'MerchantException';
    const SOAP_CLIENT_ERROR_PROCESSING = 'ProcessingException';

    private bool $debug;

    /**
     * @param array  $curlOptions CURL config values
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
     * @param \DOMNode $element
     */
    public function dumpDOMElement(\DOMNode $element)
    {
        var_dump($element->ownerDocument->saveXML($element));
    }

    /**
     * @param \DOMDocument $responseDoc
     *
     * @return Error|null
     * @throws \Exception
     */
    private function checkForSoapFault(\DOMDocument $responseDoc): Error|null
    {
        return Error::createFromSoapFault($responseDoc);
    }

    /**
     * @param AbstractRequest $payload
     *
     * @return \DOMDocument|Error
     *
     * @throws \Exception
     */
    private function soapCall(AbstractRequest $payload): \DOMDocument|Error
    {
        $request = $payload->getDocument();

        $envelope = $request->createElementNS('http://schemas.xmlsoap.org/soap/envelope/', 'SOAP-ENV:Envelope');
        $envelope->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ns1', self::NAMESPACE_N1);
        $envelope->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ns2', self::NAMESPACE_N2);
        $envelope->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ns3', self::NAMESPACE_N3);

        $body = $request->createElement('SOAP-ENV:Body');
        $body->appendChild($payload->getElement());
        $envelope->appendChild($body);

        $request->appendChild($envelope);
        $xml = $request->saveXML();

        if ($this->debug) {
            var_dump($xml);
        }

        $response = $this->doRequest($xml);

        if ($this->debug) {
            var_dump($response);
        }

        if ($response === false) {
            throw new \Exception($this->getErrorMessage());
        }

        if (empty($response)) {
            throw new \Exception('Empty API response received');
        }

        $responseDoc = new \DOMDocument('1.0', 'UTF-8');
        $responseDoc->loadXML($response);

        $errorResponse = $this->checkForSoapFault($responseDoc);

        return $errorResponse !== null ? $errorResponse : $responseDoc;
    }

    /**
     * @param ActionRequest $actionRequest
     *
     * @return \DOMDocument|Error
     */
    public function IPGApiAction(ActionRequest $actionRequest): \DOMDocument|Error
    {
        return $this->soapCall($actionRequest);
    }


    /**
     * @param OrderRequest $orderRequest
     *
     * @return \DOMDocument|Error
     */
    public function IPGApiOrder(OrderRequest $orderRequest): \DOMDocument|Error
    {
        return $this->soapCall($orderRequest);
    }

}
