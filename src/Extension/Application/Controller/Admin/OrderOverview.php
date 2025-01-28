<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Extension\Application\Controller\Admin;

use OxidSolutionCatalysts\TeleCash\Exception\TeleCashException;
use OxidSolutionCatalysts\TeleCash\Traits\AdminSendOrder;

class OrderOverview extends OrderOverview_parent
{
    use AdminSendOrder;

    /**
     * Constructor for OrderOverview.
     *
     * Initializes a new instance of the OrderOverview class. This constructor can be used
     * in both production and test environments due to its flexible parameter configuration.
     *
     * @param bool $initParent Whether to initialize the parent BaseModel.
     *                          Set to false in test environment to avoid
     *                          OXID framework dependencies. Default is true.
     */
    public function __construct(
        bool $initParent = true
    )
    {
        if ($initParent) {
            parent::__construct();
        }

        $this->setContainer($this->getContainer());
    }

    /**
     * Core-Extension - var-types and return value only in doc-block
     * {@inheritDoc}
     *
     * @return void
     * @throws TeleCashException
     */
    public function sendorder()
    {
        parent::sendorder();
        $this->teleCashDoChargeOrder();
    }
}
