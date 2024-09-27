<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

use OxidSolutionCatalysts\TeleCash\Core\Module;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleSettingsServiceInterface;

/**
 * Metadata version
 */
$sMetadataVersion = '2.1';

/**
 * Module information
 */
$aModule = [
    'id'          => Module::MODULE_ID,
    'title'       => 'Payment-Module for Payment-Provider Telecash',
    'description' =>  'This module provides the integration of the payment provider Telecash.',
    'thumbnail'   => 'pictures/logo.png',
    'version'     => '1.0.0',
    'author'      => 'OXID eSales AG',
    'url'         => '',
    'email'       => '',
    'extend'      => [
    ],
    'controllers' => [
    ],
    'events' => [
        'onActivate' => '\OxidSolutionCatalysts\TeleCash\Core\ModuleEvents::onActivate',
        'onDeactivate' => '\OxidSolutionCatalysts\TeleCash\Core\ModuleEvents::onDeactivate'
    ],
    'settings' => [
        //TODO: add help texts for settings to explain possibilities and point out which ones only serve as example
        /** Main */
        [
            'group'       => 'osctelecash_api',
            'name'        => ModuleSettingsServiceInterface::API_MODE,
            'type'        => 'select',
            'constraints' => ModuleSettingsServiceInterface::API_MODE_SANDBOX . '|' . ModuleSettingsServiceInterface::API_MODE_LIVE,
            'value'       => ModuleSettingsServiceInterface::API_MODE_SANDBOX
        ],
    ],
];
