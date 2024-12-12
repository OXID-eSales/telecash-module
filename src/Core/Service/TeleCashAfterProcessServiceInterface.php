<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Core\Service;

interface TeleCashAfterProcessServiceInterface
{
    /**
     * @param string $orderId
     * @param float $amount
     * @return void
     */
    public function doChargeOrder(string $orderId, float $amount): void;
}
