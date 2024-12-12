<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;

use DOMException;
use Exception;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\Payment;
use OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action\Validation;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Error;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;
use RuntimeException;

/**
 * Class ValidateHostedData
 */
class ValidateHostedData extends Action
{
    /**
     * @param OrderService $service
     * @param Payment $payment
     * @throws DOMException
     */
    public function __construct(OrderService $service, private readonly Payment $payment)
    {
        parent::__construct($service);

        try {
            // Get Action element from parent
            $actionElement = $this->element->getElementsByTagName('ns2:Action')->item(0);
            if (!$actionElement) {
                throw new RuntimeException('Action element not found');
            }

            // Create Validate element
            $validateElement = $this->document->createElement('ns2:Validate');

            // Get and append payment data
            $paymentData = $this->payment->getXML($this->document);
            $validateElement->appendChild($paymentData);

            // Append to Action element
            $actionElement->appendChild($validateElement);
        } catch (Exception $e) {
            throw new RuntimeException('Failed to create ValidateHostedData XML: ' . $e->getMessage());
        }
    }

    /**
     * @return Validation|Error
     * @throws Exception
     */
    public function validate(): Validation|Error
    {
        $response = $this->service->IPGApiAction($this);

        return $response instanceof Error ? $response : new Validation($response);
    }
}
