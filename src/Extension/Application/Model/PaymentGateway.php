<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Extension\Application\Model;

use Exception;
use OxidEsales\Eshop\Application\Model\Order as oxOrder;
use OxidSolutionCatalysts\TeleCash\Exception\TeleCashException;
use OxidSolutionCatalysts\TeleCash\Traits\ModelGetter;
use OxidSolutionCatalysts\TeleCash\Traits\ServiceContainer;

class PaymentGateway extends PaymentGateway_parent
{
    use ModelGetter;
    use ServiceContainer;

    /**
     * Constructor for PaymentGateway.
     *
     * Initializes a new instance of the PaymentGateway class. This constructor can be used
     * in both production and test environments due to its flexible parameter configuration.
     *
     * @param bool $initParent  Whether to initialize the parent BaseModel.
     *                          Set to false in test environment to avoid
     *                          OXID framework dependencies. Default is true.
     */
    public function __construct(
        bool $initParent = true
    ) {
        if ($initParent) {
            parent::__construct();
        }

        $this->setContainer($this->getContainer());
    }

    /**
     * OXID-Core
     * {@inheritDoc}
     *
     * Executes payment, returns true on success.
     *
     * @param double $dAmount Goods amount
     * @param object $oOrder User ordering object
     *
     * @return bool
     * @throws TeleCashException
     */
    public function executePayment($dAmount, &$oOrder)
    {
        /** @var oxOrder $oOrder */
        $result = parent::executePayment($dAmount, $oOrder);

        if ($result) {
            $result = $this->executeTeleCashPayment($oOrder);
        }

        return $result;
    }

    /**
     * Executes TeleCash-Payment, returns true on success and true if it is no TeleCashPayment
     *
     * @param oxOrder $order User ordering object
     *
     * @return bool
     * @throws TeleCashException
     * @throws Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function executeTeleCashPayment(oxOrder $order): bool
    {
        /** @var Payment $payment */
        /** @var Order $order */
        $payment = $this->getOxidPaymentModel();
        $paymentId = $order->getFieldStringData('oxpaymenttype');
        $orderId = $order->getId();

        $payment->load($paymentId);

        if (!$payment->isTeleCashPayment()) {
            return true;
        }

        $telecashConnect = $this->getTeleCashConnect();
        $telecashConnect->setResponseData($_POST);
        if (!$telecashConnect->isValidResponse()) {
            return false;
        }

        // save the transaction-Result
        $transactionResult = $telecashConnect->getTransactionResult();
        $teleCashOrder = $this->getTeleCashOrderModel($orderId);
        $teleCashOrder->setTransactionResult($transactionResult);
        return (bool) $teleCashOrder->save();
    }
}
