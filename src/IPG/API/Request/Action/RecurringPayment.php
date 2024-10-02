<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;

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

    /** @var string|null $function */
    private string|null $function;
    /** @var Payment|null $payment */
    private Payment|null $payment;
    /** @var RecurringPaymentInformation|null $paymentInformation */
    private RecurringPaymentInformation|null $paymentInformation;
    /** @var string|null orderId */
    private string|null $orderId;

    /**
     * @param OrderService                $service
     * @param string|null                 $function
     * @param Payment|null                $payment
     * @param RecurringPaymentInformation|null $paymentInformation
     * @param string|null                 $orderId
     */
    public function __construct(
        OrderService $service,
        string|null $function,
        Payment|null $payment = null,
        RecurringPaymentInformation|null $paymentInformation = null,
        string|null $orderId = null
    ) {
        parent::__construct($service);

        $this->function           = $function;
        $this->payment            = $payment;
        $this->paymentInformation = $paymentInformation;
        $this->orderId            = $orderId;

        $xml                   = $this->document->createElement('ns2:RecurringPayment');
        $function              = $this->document->createElement('ns2:Function');
        $function->textContent = (string)$this->function;
        $xml->appendChild($function);

        if ($this->function === self::FUNCTION_MODIFY || $this->function === self::FUNCTION_CANCEL) {
            $orderId              = $this->document->createElement('ns2:OrderId');
            $orderId->textContent = (string)$this->orderId;
            $xml->appendChild($orderId);
        }

        if ($this->paymentInformation !== null) {
            $paymentInformation = $this->paymentInformation->getXML($this->document);
            $xml->appendChild($paymentInformation);
        }

        if ($this->payment !== null) {
            $payment = $this->payment->getXML($this->document);
            $xml->appendChild($payment);
        }

        $item0 = $this->element->getElementsByTagName('ns2:Action')->item(0);
        if ($item0) {
            $item0->appendChild($xml);
        }
    }

    /**
     * Execute this action
     *
     * @return ConfirmRecurring|Sell|Error
     */
    protected function execute(): ConfirmRecurring|Sell|Error
    {
        $response = $this->service->IPGApiAction($this);

        if ($response instanceof Error) {
            return $response;
        }

        if ($this->function === self::FUNCTION_INSTALL) {
            return new Sell($response);
        }

        return new ConfirmRecurring($response);
    }
}
