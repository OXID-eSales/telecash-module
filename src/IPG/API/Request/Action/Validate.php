<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;

use DOMException;
use Exception;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\CreditCardData;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\Payment;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\TransactionDetails;
use OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Error;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action\Validation;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;
use RuntimeException;

/**
 * Class Validate
 */
class Validate extends Action
{
    /**
     * @param OrderService $service
     * @param CreditCardData $creditCardData
     * @param float $amount
     * @param string|null $text
     * @throws DOMException
     */
    public function __construct(
        OrderService $service,
        private readonly CreditCardData $creditCardData,
        private readonly float $amount = 1.0,
        private readonly ?string $text = null
    ) {
        parent::__construct($service);

        try {
            // Get Action element from parent
            $actionElement = $this->element->getElementsByTagName('ns2:Action')->item(0);
            if (!$actionElement) {
                throw new RuntimeException('Action element not found');
            }

            // Create Validate element
            $validateElement = $this->document->createElement('ns2:Validate');

            // Set namespace and get credit card data
            $this->creditCardData->setNamespaceShort('ns2');
            $ccData = $this->creditCardData->getXML($this->document);
            $validateElement->appendChild($ccData);

            // Add payment data if amount > 1.0
            if ($this->amount > 1.0) {
                $payment = new Payment(null, $this->amount);
                $paymentData = $payment->getXML($this->document);
                $validateElement->appendChild($paymentData);
            }

            // Add transaction details if text is provided
            if (!empty($this->text)) {
                $transactionDetails = new TransactionDetails('ns2', $this->text);
                $transactionDetailsData = $transactionDetails->getXML($this->document);
                $validateElement->appendChild($transactionDetailsData);
            }

            // Append to Action element
            $actionElement->appendChild($validateElement);
        } catch (Exception $e) {
            throw new RuntimeException('Failed to create Validate XML: ' . $e->getMessage());
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
