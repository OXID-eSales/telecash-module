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
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashConstants;

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
     * @param OrderService $service
     * @param string|null $function
     * @param Payment|null $payment
     * @param RecurringPaymentInformation|null $paymentInformation
     * @param string|null $orderId
     * @throws \DOMException
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

        $xml                   = $this->document->createElement(TeleCashConstants::PREF_A1 . 'RecurringPayment');
        $functionEl              = $this->document->createElement(TeleCashConstants::PREF_A1 . 'Function');
        $functionEl->textContent = (string)$this->function;
        $xml->appendChild($functionEl);

        if ($this->function === self::FUNCTION_MODIFY || $this->function === self::FUNCTION_CANCEL) {
            $orderIdEl              = $this->document->createElement(TeleCashConstants::PREF_A1 . 'OrderId');
            $orderIdEl->textContent = (string)$this->orderId;
            $xml->appendChild($orderIdEl);
        }

        if ($this->paymentInformation !== null) {
            $paymentInformationEl = $this->paymentInformation->getXML($this->document);
            $xml->appendChild($paymentInformationEl);
        }

        if ($this->payment !== null) {
            $paymentEl = $this->payment->getXML($this->document);
            $xml->appendChild($paymentEl);
        }

        $item0 = $this->element->getElementsByTagName(TeleCashConstants::PREF_A1 . 'Action')->item(0);
        $item0?->appendChild($xml);
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

        if ($response instanceof Error) {
            return $response;
        }

        if ($this->function === self::FUNCTION_INSTALL) {
            return new Sell($response);
        }

        return new ConfirmRecurring($response);
    }
}
