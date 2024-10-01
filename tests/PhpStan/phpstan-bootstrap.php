<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

use OxidEsales\Eshop\Application\Controller\Admin\ModuleConfiguration;
use OxidSolutionCatalysts\TeleCash\Extension\Application\Controller\Admin\ModuleConfiguration_parent;

class_alias(
    ModuleConfiguration::class,
    ModuleConfiguration_parent::class
);
