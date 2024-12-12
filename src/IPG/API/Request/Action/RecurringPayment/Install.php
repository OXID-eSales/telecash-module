<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action\RecurringPayment;

use DOMException;
use Exception;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\Payment;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\RecurringPaymentInformation;
use OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action\ConfirmRecurring;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Error;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Order\Sell;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;

/**
 * Class Install
 */
class Install extends Action\RecurringPayment
{
    /**
     * @param OrderService $service
     * @param Payment|null $payment
     * @param RecurringPaymentInformation|null $paymentInformation
     * @throws DOMException
     */
    public function __construct(
        OrderService $service,
        Payment $payment = null,
        RecurringPaymentInformation $paymentInformation = null
    ) {
        parent::__construct($service, self::FUNCTION_INSTALL, $payment, $paymentInformation);
    }

    /**
     * Install a recurring payment
     *
     * @return ConfirmRecurring|Sell|Error
     * @throws Exception
     */
    public function install(): ConfirmRecurring|Sell|Error
    {
        return $this->execute();
    }
}
