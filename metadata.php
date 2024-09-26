<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

use OxidSolutionCatalysts\TeleCash\Core\Module;
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
    'description' =>  '',
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
    ],
];
