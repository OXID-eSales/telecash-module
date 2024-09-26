<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

class_alias(
    \OxidEsales\Eshop\Application\Model\User::class,
    \OxidSolutionCatalysts\TeleCash\Extension\Model\User_parent::class
);

class_alias(
    \OxidEsales\Eshop\Application\Controller\StartController::class,
    \OxidSolutionCatalysts\TeleCash\Extension\Controller\StartController_parent::class
);

class_alias(
    \OxidEsales\Eshop\Application\Model\Basket::class,
    \OxidSolutionCatalysts\TeleCash\Extension\Model\Basket_parent::class
);
