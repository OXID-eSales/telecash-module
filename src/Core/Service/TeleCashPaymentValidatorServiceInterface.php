<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Core\Service;

interface TeleCashPaymentValidatorServiceInterface
{
    public function checkIfPaymentExists(
        string $paymentId,
        string $ident,
        string $captureType
    ): bool;
}
