<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Traits;

use OxidSolutionCatalysts\TeleCash\Core\Module;
use OxidSolutionCatalysts\TeleCash\Core\Service\TeleCashAfterProcessServiceInterface;
use OxidSolutionCatalysts\TeleCash\Exception\TeleCashException;
use OxidSolutionCatalysts\TeleCash\Extension\Application\Model\Order;
use OxidSolutionCatalysts\TeleCash\Extension\Application\Model\Payment;
use OxidEsales\Eshop\Application\Model\Order as oxOrder;

/**
 * Convenience trait to provide teleCashDoChargeOrder for OrderOverview & OrderMain
 */
trait AdminSendOrder
{
    use ModelGetter;
    use ServiceContainer;

    private ?string $teleCashCaptureType = null;

    /**
     * @throws TeleCashException
     */
    protected function teleCashDoChargeOrder(): void
    {
        /** @var Order $order */
        $order = $this->getOxidOrderModel();
        $order->load($this->getEditObjectId());
        if (
            $order->isLoaded() &&
            $order->isTeleCashOrder() &&
            $this->getTeleCashCaptureType($order) === Module::TELECASH_CAPTURE_TYPE_ONDELIVERY
        ) {
            $teleCashOrder = $order->getTeleCashOrder();
            if ($teleCashOrder) {
                $afterProcessService = $this->getRequiredService(
                    TeleCashAfterProcessServiceInterface::class,
                    'TeleCashAfterProcessService'
                );

                $afterProcessService->doChargeOrder(
                    $teleCashOrder->getId(),
                    $teleCashOrder->getOxidChargeTotal()
                );
            }
        }
    }

    /**
     * @throws TeleCashException
     */
    private function getTeleCashCaptureType(oxOrder $order): string
    {
        if (is_null($this->teleCashCaptureType)) {
            $this->teleCashCaptureType = '';
            $payment = $this->getOxidPaymentModel();
            /** @var Order $order */
            $payment->load($order->getFieldStringData('oxpaymenttype'));
            /** @var Payment $payment */
            if ($payment->isLoaded() && $payment->isTeleCashPayment()) {
                $teleCashPayment = $payment->getTeleCashPayment();
                $this->teleCashCaptureType = $teleCashPayment ?
                    $teleCashPayment->getTeleCashCaptureType() :
                    '';
            }
        }
        return $this->teleCashCaptureType;
    }
}
