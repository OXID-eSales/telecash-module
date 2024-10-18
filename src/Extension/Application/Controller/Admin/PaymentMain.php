<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Extension\Application\Controller\Admin;

use OxidSolutionCatalysts\TeleCash\Traits\ServiceContainer;

class PaymentMain extends PaymentMain_parent
{
    use ServiceContainer;

    /**
     * OXID-Core
     * @inheritDoc
     * @return string
     */
    public function save()
    {
        $result = parent::save();

        return $result;
    }
}
