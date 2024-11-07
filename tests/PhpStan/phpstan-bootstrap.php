<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

use OxidEsales\Eshop\Application\Controller\Admin\ModuleConfiguration;
use OxidEsales\Eshop\Application\Controller\Admin\PaymentMain;
use OxidEsales\Eshop\Application\Controller\OrderController;
use OxidEsales\Eshop\Application\Model\Address;
use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Application\Model\PaymentList;
use OxidEsales\Eshop\Application\Model\State;
use OxidEsales\Eshop\Application\Model\User;
use OxidSolutionCatalysts\TeleCash\Extension\Application\Controller\Admin\ModuleConfiguration_parent;
use OxidSolutionCatalysts\TeleCash\Extension\Application\Controller\Admin\PaymentMain_parent;
use OxidSolutionCatalysts\TeleCash\Extension\Application\Controller\OrderController_parent;
use OxidSolutionCatalysts\TeleCash\Extension\Application\Model\Address_parent;
use OxidSolutionCatalysts\TeleCash\Extension\Application\Model\Country_parent;
use OxidSolutionCatalysts\TeleCash\Extension\Application\Model\Payment_parent;
use OxidSolutionCatalysts\TeleCash\Extension\Application\Model\PaymentList_parent;
use OxidSolutionCatalysts\TeleCash\Extension\Application\Model\State_parent;
use OxidSolutionCatalysts\TeleCash\Extension\Application\Model\User_parent;

// Admin Controller

class_alias(
    ModuleConfiguration::class,
    ModuleConfiguration_parent::class
);

class_alias(
    PaymentMain::class,
    PaymentMain_parent::class
);

// Frontend Controller

class_alias(
    OrderController::class,
    OrderController_parent::class
);

// Models

class_alias(
    Address::class,
    Address_parent::class
);

class_alias(
    Country::class,
    Country_parent::class
);

class_alias(
    Payment::class,
    Payment_parent::class
);

class_alias(
    PaymentList::class,
    PaymentList_parent::class
);

class_alias(
    State::class,
    State_parent::class
);

class_alias(
    User::class,
    User_parent::class
);
