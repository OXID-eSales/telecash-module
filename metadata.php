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
    'extend'      => [
    ],
    'controllers' => [
    ],
    'events' => [
        'onActivate' => '\OxidSolutionCatalysts\TeleCash\Core\ModuleEvents::onActivate',
        'onDeactivate' => '\OxidSolutionCatalysts\TeleCash\Core\ModuleEvents::onDeactivate'
    ],
    'settings' => [
        [
            'group'       => 'osctelecash_api',
            'name'        => ModuleSettingsServiceInterface::API_MODE,
            'type'        => 'select',
            'constraints' => ModuleSettingsServiceInterface::API_MODE_SANDBOX . '|' . ModuleSettingsServiceInterface::API_MODE_LIVE,
            'value'       => ModuleSettingsServiceInterface::API_MODE_SANDBOX
        ],
        [
            'group' => 'osctelecash_api',
            'name'  => ModuleSettingsServiceInterface::STORE_ID,
            'type'  => 'string',
            'value' => '',
        ],
        [
            'group' => 'osctelecash_api',
            'name'  => ModuleSettingsServiceInterface::USER_ID,
            'type'  => 'string',
            'value' => '',
        ],
        [
            'group' => 'osctelecash_api',
            'name'  => ModuleSettingsServiceInterface::BASIC_AUTH_PASSWORD,
            'type'  => 'string',
            'value' => '',
        ],
        [
            'group' => 'osctelecash_api',
            'name'  => ModuleSettingsServiceInterface::CLIENT_CERT_INSTALL_PASSWORD,
            'type'  => 'string',
            'value' => '',
        ],
        [
            'group' => 'osctelecash_api',
            'name'  => ModuleSettingsServiceInterface::CLIENT_CERT_PRIVATEKEY_PASSWORD,
            'type'  => 'string',
            'value' => '',
        ],
        // these options are hidden, so the group is null
        [
            'group' => null,
            'name'  => ModuleSettingsServiceInterface::CLIENT_CERT_P12_FILE,
            'type'  => 'string',
            'value' => '',
        ],
        [
            'group' => null,
            'name'  => ModuleSettingsServiceInterface::CLIENT_CERT_PRIVATEKEY_FILE,
            'type'  => 'string',
            'value' => '',
        ],
        [
            'group' => null,
            'name'  => ModuleSettingsServiceInterface::CLIENT_CERT_PEM_FILE,
            'type'  => 'string',
            'value' => '',
        ],
        [
            'group' => null,
            'name'  => ModuleSettingsServiceInterface::TRUST_ANCHOR_PEM_FILE,
            'type'  => 'string',
            'value' => '',
        ],
    ]
];
