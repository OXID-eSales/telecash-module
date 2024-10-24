<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Traits;

use OxidSolutionCatalysts\TeleCash\Application\Model\TeleCashPayment;
use OxidSolutionCatalysts\TeleCash\Core\Service\OxNewService;
use OxidSolutionCatalysts\TeleCash\Core\Service\TeleCashPaymentValidatorServiceInterface;

/**
 * Convenience trait to work with Controller-Models.
 */
trait ControllerGetter
{
    private function getTeleCashPaymentModel(): ?TeleCashPayment
    {
        $oxNewService = $this->getServiceFromContainer(OxNewService::class);
        if (!$oxNewService instanceof OxNewService) {
            return null;
        }

        $validator = $this->getRequiredService(
            TeleCashPaymentValidatorServiceInterface::class,
            'TeleCashPaymentValidatorService'
        );
        return $oxNewService->oxNew(TeleCashPayment::class, [$validator]);
    }
}
