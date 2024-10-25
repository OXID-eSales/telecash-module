<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Traits;

use OxidEsales\Eshop\Application\Model\Payment;
use OxidSolutionCatalysts\TeleCash\Application\Model\TeleCashPayment;
use OxidSolutionCatalysts\TeleCash\Core\Service\OxNewService;
use OxidSolutionCatalysts\TeleCash\Core\Service\TeleCashPaymentValidatorServiceInterface;

/**
 * Convenience trait to work with Controller-Models.
 */
trait ModelGetter
{
    private function getTeleCashPaymentModel(): ?TeleCashPayment
    {
        $oxNewService = $this->getOxNewService();
        if (!$oxNewService) {
            return null;
        }

        $validator = $this->getRequiredService(
            TeleCashPaymentValidatorServiceInterface::class,
            'TeleCashPaymentValidatorService'
        );
        return $oxNewService->oxNew(TeleCashPayment::class, [$validator]);
    }

    private function getOxidPaymentModel(): ?Payment
    {
        return $this->getOxNewService()?->oxNew(Payment::class);
    }

    private function getOxNewService(): ?OxNewService
    {
        return $this->getServiceFromContainer(OxNewService::class);
    }
}
