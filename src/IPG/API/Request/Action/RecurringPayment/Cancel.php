<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action\RecurringPayment;

use OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action\ConfirmRecurring;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Error;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;

/**
 * Class Cancel
 */
class Cancel extends Action\RecurringPayment
{
    /**
     * @param OrderService $service
     * @param string       $orderId
     */
    public function __construct(
        OrderService $service,
        string $orderId
    ) {
        parent::__construct($service, self::FUNCTION_CANCEL, null, null, $orderId);
    }

    /**
     * Cancel a recurring payment
     *
     * @return ConfirmRecurring|Error
     */
    public function cancel(): ConfirmRecurring|Error
    {
        return $this->execute();
    }
}
