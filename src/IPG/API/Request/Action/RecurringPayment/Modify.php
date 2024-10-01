<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action\RecurringPayment;

use OxidSolutionCatalysts\TeleCash\IPG\API\Model\Payment;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\RecurringPaymentInformation;
use OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action\ConfirmRecurring;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Error;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;

/**
 * Class Modify
 */
class Modify extends Action\RecurringPayment
{
    /**
     * @param OrderService                $service
     * @param string                      $orderId
     * @param Payment                     $payment
     * @param RecurringPaymentInformation $paymentInformation
     */
    public function __construct(
        OrderService $service,
        string $orderId,
        Payment $payment = null,
        RecurringPaymentInformation $paymentInformation = null
    ) {
        parent::__construct($service, self::FUNCTION_MODIFY, $payment, $paymentInformation, $orderId);
    }

    /**
     * Modify a recurring payment
     *
     * @return ConfirmRecurring|Error
     */
    public function modify(): ConfirmRecurring|Error
    {
        return $this->execute();
    }
}
