<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Core\Service;

use OxidSolutionCatalysts\TeleCash\IPG\TeleCash;

interface TeleCashAPIServiceInterface
{
    public function getTeleCashAPI(): TeleCash;
}
