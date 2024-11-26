<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Application\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminController;
use OxidSolutionCatalysts\TeleCash\Core\Module;

class OrderTeleCash extends AdminController
{
    protected $_sThisTemplate = '@' . Module::MODULE_ID . '/admin/order_telecash';

    /** @inheritdoc */
    public function render()
    {
        parent::render();

        $oxId = $this->getEditObjectId();
        if ($oxId) {
            $this->addTplParam('oxid', $oxId);
        }

        return "order_downloads";
    }
}
