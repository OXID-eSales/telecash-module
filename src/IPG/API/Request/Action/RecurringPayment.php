<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;

use Exception;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\Payment;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\RecurringPaymentInformation;
use OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action\ConfirmRecurring;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Error;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Order\Sell;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;

/**
 * Class RecurringPayment
 */
abstract class RecurringPayment extends Action
{
    public const FUNCTION_INSTALL = 'install';
    public const FUNCTION_MODIFY  = 'modify';
    public const FUNCTION_CANCEL  = 'cancel';

    /**
     * @param OrderService $service
     * @param string|null $function
     * @param Payment|null $payment
     * @param RecurringPaymentInformation|null $paymentInformation
     * @param string|null $orderId
     * @throws \DOMException
     */
    public function __construct(
        OrderService $service,
        private readonly ?string $function,
        private readonly ?Payment $payment = null,
        private readonly ?RecurringPaymentInformation $paymentInformation = null,
        private readonly ?string $orderId = null
    ) {
        parent::__construct($service);

        try {
            // Get Action element from parent
            $actionElement = $this->element->getElementsByTagName('ns2:Action')->item(0);
            if (!$actionElement) {
                throw new \RuntimeException('Action element not found');
            }

            // Create RecurringPayment element
            $recurringElement = $this->document->createElement('ns2:RecurringPayment');

            // Add function
            $functionElement = $this->document->createElement('ns2:Function');
            $functionElement->textContent = $this->function ?? '';
            $recurringElement->appendChild($functionElement);

            // Add OrderId for modify and cancel
            if (
                ($this->function === self::FUNCTION_MODIFY || $this->function === self::FUNCTION_CANCEL)
                && $this->orderId !== null
            ) {
                $orderIdElement = $this->document->createElement('ns2:OrderId');
                $orderIdElement->textContent = $this->orderId;
                $recurringElement->appendChild($orderIdElement);
            }

            // Add optional payment information
            if ($this->paymentInformation !== null) {
                $recurringElement->appendChild($this->paymentInformation->getXML($this->document));
            }

            // Add optional payment
            if ($this->payment !== null) {
                $recurringElement->appendChild($this->payment->getXML($this->document));
            }

            $actionElement->appendChild($recurringElement);
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to create RecurringPayment XML: ' . $e->getMessage());
        }
    }

    /**
     * Execute this action
     *
     * @return ConfirmRecurring|Sell|Error
     * @throws Exception
     */
    protected function execute(): ConfirmRecurring|Sell|Error
    {
        $response = $this->service->IPGApiAction($this);
        return match (true) {
            $response instanceof Error => $response,
            $this->function === self::FUNCTION_INSTALL => new Sell($response),
            default => new ConfirmRecurring($response),
        };
    }
}
