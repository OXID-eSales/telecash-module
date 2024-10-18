<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

use OxidEsales\Eshop\Application\Controller\Admin\ModuleConfiguration;
use OxidEsales\Eshop\Application\Controller\Admin\PaymentMain;
use OxidEsales\Eshop\Application\Model\Payment;
use OxidSolutionCatalysts\TeleCash\Extension\Application\Controller\Admin\ModuleConfiguration_parent;
use OxidSolutionCatalysts\TeleCash\Extension\Application\Controller\Admin\PaymentMain_parent;
use OxidSolutionCatalysts\TeleCash\Extension\Application\Model\Payment_parent;

class_alias(
    ModuleConfiguration::class,
    ModuleConfiguration_parent::class
);

class_alias(
    Payment::class,
    Payment_parent::class
);

class_alias(
    PaymentMain::class,
    PaymentMain_parent::class
);
