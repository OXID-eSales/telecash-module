<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Extension\Application\Controller\Admin;

use OxidSolutionCatalysts\TeleCash\Exception\TeleCashException;
use OxidSolutionCatalysts\TeleCash\Traits\AdminSendOrder;

class OrderMain extends OrderMain_parent
{
    use AdminSendOrder;

    /**
     * Core-Extension - var-types and return value only in doc-block
     * {@inheritDoc}
     *
     * @return void
     * @throws TeleCashException
     */
    public function sendOrder()
    {
        parent::sendOrder();
        $this->teleCashDoChargeOrder();
    }
}
