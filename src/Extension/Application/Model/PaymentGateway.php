<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Extension\Application\Model;

use OxidEsales\Eshop\Application\Model\Order;
use OxidSolutionCatalysts\TeleCash\Exception\TeleCashException;
use OxidSolutionCatalysts\TeleCash\Traits\ModelGetter;
use OxidSolutionCatalysts\TeleCash\Traits\ServiceContainer;

class PaymentGateway extends PaymentGateway_parent
{
    use ModelGetter;
    use ServiceContainer;

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
        /** @var Order $oOrder */
        $result = parent::executePayment($dAmount, $oOrder);

        if ($result) {
            $result = $this->executeTeleCashPayment($dAmount, $oOrder);
        }

        return $result;
    }

    /**
     * Executes TeleCash-Payment, returns true on success and true if it is no TeleCashPayment
     *
     * @param float $amount Goods amount
     * @param Order $order User ordering object
     *
     * @return bool
     * @throws TeleCashException
     * TODO remove PHPMD.UnusedLocalVariable, UnusedFormalParameter
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function executeTeleCashPayment(float $amount, Order $order): bool
    {

        /** @var Payment $payment */
        $payment = $order->getPayment();
        if (!$payment->isTeleCashPayment()) {
            return true;
        }

        $telecashConnect = $this->getTeleCashConnect();

        if (!$telecashConnect->isValidResponse($_POST)) {
            return false;
        }

        $result = false;

        $telecashConnect->addPostData($_POST);

        /** TODO follow up the work ... */
        $transactionResult = $telecashConnect->getTransactionResult();

        return $result;
    }
}
