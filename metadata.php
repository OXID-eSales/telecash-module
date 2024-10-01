<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

use OxidEsales\Eshop\Application\Controller\Admin\ModuleConfiguration as oxModuleConfiguration;
use OxidSolutionCatalysts\TeleCash\Core\Module;
use OxidSolutionCatalysts\TeleCash\Extension\Application\Controller\Admin\ModuleConfiguration;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleFileSettingsServiceInterface;
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
    'title'       => [
        'de' => 'Zahlungs-Module für Zahlungsdienstleisters Telecash',
        'en' => 'Payment-Module for Payment-Provider Telecash',
    ],
    'description' => [
        'de' => 'Dieses Modul ermöglicht die Integration des Zahlungsdienstleisters Telecash.',
        'en' => 'This module provides the integration of the payment provider Telecash.',
    ],
    'thumbnail'   => 'pictures/logo.png',
    'version'     => '1.0.0',
    'author'      => 'OXID eSales AG',
    'url'         => '',
    'email'       => '',
    'controllers' => [
    ],
    'events' => [
        'onActivate' => '\OxidSolutionCatalysts\TeleCash\Core\ModuleEvents::onActivate',
        'onDeactivate' => '\OxidSolutionCatalysts\TeleCash\Core\ModuleEvents::onDeactivate'
    ],
    'extend' => [
        oxModuleConfiguration::class => ModuleConfiguration::class,
    ],
    'settings' => [
        [
            'group'       => ModuleSettingsServiceInterface::MODULE_CONFIG_API_VARGROUP,
            'name'        => ModuleSettingsServiceInterface::API_MODE,
            'type'        => 'select',
            'constraints' => ModuleSettingsServiceInterface::API_MODE_SANDBOX . '|' . ModuleSettingsServiceInterface::API_MODE_LIVE,
            'value'       => ModuleSettingsServiceInterface::API_MODE_SANDBOX
        ],
        [
            'group' => ModuleSettingsServiceInterface::MODULE_CONFIG_API_VARGROUP,
            'name'  => ModuleSettingsServiceInterface::STORE_ID,
            'type'  => 'str',
            'value' => '',
    ],
        [
            'group' => ModuleSettingsServiceInterface::MODULE_CONFIG_API_VARGROUP,
            'name'  => ModuleSettingsServiceInterface::USER_ID,
            'type'  => 'str',
            'value' => '',
        ],
        [
            'group' => ModuleSettingsServiceInterface::MODULE_CONFIG_API_VARGROUP,
            'name'  => ModuleSettingsServiceInterface::BASIC_AUTH_PASSWORD,
            'type'  => 'str',
            'value' => '',
        ],
        [
            'group' => ModuleSettingsServiceInterface::MODULE_CONFIG_API_VARGROUP,
            'name'  => ModuleSettingsServiceInterface::CLIENT_CERT_INSTALL_PASSWORD,
            'type'  => 'str',
            'value' => '',
        ],
        [
            'group' => ModuleSettingsServiceInterface::MODULE_CONFIG_API_VARGROUP,
            'name'  => ModuleSettingsServiceInterface::CLIENT_CERT_PRIVATEKEY_PASSWORD,
            'type'  => 'str',
            'value' => '',
        ],
        // these options are hidden, so the group is null
        [
            'group' => null,
            'name'  => ModuleFileSettingsServiceInterface::CLIENT_CERT_P12_FILE,
            'type'  => 'str',
            'value' => '',
        ],
        [
            'group' => null,
            'name'  => ModuleFileSettingsServiceInterface::CLIENT_CERT_PRIVATEKEY_FILE,
            'type'  => 'str',
            'value' => '',
        ],
        [
            'group' => null,
            'name'  => ModuleFileSettingsServiceInterface::CLIENT_CERT_PEM_FILE,
            'type'  => 'str',
            'value' => '',
        ],
        [
            'group' => null,
            'name'  => ModuleFileSettingsServiceInterface::TRUST_ANCHOR_PEM_FILE,
            'type'  => 'str',
            'value' => '',
        ],
    ]
];
