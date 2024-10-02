<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;

use OxidSolutionCatalysts\TeleCash\IPG\API\Model\Payment;
use OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action\Validation;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Error;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;

/**
 * Class ValidateHostedData
 */
class ValidateHostedData extends Action
{
    /**
     * @param OrderService $service
     * @param Payment      $payment
     */
    public function __construct(OrderService $service, Payment $payment)
    {
        parent::__construct($service);

        $xml    = $this->document->createElement('ns2:Validate');
        $ccData = $payment->getXML($this->document);
        $xml->appendChild($ccData);
        $item0 = $this->element->getElementsByTagName('ns2:Action')->item(0);
        if ($item0) {
            $item0->appendChild($xml);
        }
    }

    /**
     * @return Validation|Error
     * @throws \Exception
     */
    public function validate(): Validation|Error
    {
        $response = $this->service->IPGApiAction($this);

        return $response instanceof Error ? $response : new Validation($response);
    }
}
