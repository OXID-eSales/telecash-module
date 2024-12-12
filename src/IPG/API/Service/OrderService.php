<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Service;

use DOMDocument;
use DOMNode;
use Exception;
use OxidSolutionCatalysts\TeleCash\Core\Service\Logger;
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

    /**
     * @param array<int|string, mixed>  $curlOptions CURL config values
     * @param string $username    API user
     * @param string $password    API pass
     * @param Logger $logger      Logger
     */
    public function __construct(
        array $curlOptions,
        string $username,
        string $password,
        private readonly Logger $logger
    ) {
        parent::__construct($curlOptions, $username, $password);
    }

    /**
     * @param string $type
     * @param DOMNode $element
     */
    public function dumpDOMElement(string $type, DOMNode $element): void
    {
        if ($element->ownerDocument !== null) {
            $this->logger->log(
                'debug',
                'Debug: ' . $type . ': ' . $element->ownerDocument->saveXML($element)
            );
        }
    }

    public function handleDebug(string $type, string $source): void
    {
        $xml = new DOMDocument();
        $xml->loadXML($source);
        $xml->preserveWhiteSpace = false;
        $xml->formatOutput = true;

        $this->logger->log(
            'debug',
            'Debug: ' . $type . ': ' . $xml->saveXML()
        );
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

        $this->handleDebug('Request', (string)$xml);
        $response = false;
        if ($xml) {
            $response = $this->doRequest($xml);
        }
        $this->handleDebug('Response', (string)$response);

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
