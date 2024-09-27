<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleSettingsServiceInterface;

$aLang = [
    'charset' => 'UTF-8',

    # Module settings
    'SHOP_MODULE_GROUP_osctelecash_api' => 'API',

    'SHOP_MODULE_' . ModuleSettingsServiceInterface::API_MODE                                                          => 'API-Modus',
    'SHOP_MODULE_' . ModuleSettingsServiceInterface::API_MODE . '_' . ModuleSettingsServiceInterface::API_MODE_LIVE    => 'Live',
    'SHOP_MODULE_' . ModuleSettingsServiceInterface::API_MODE . '_' . ModuleSettingsServiceInterface::API_MODE_SANDBOX => 'Sandbox',

    'SHOP_MODULE_' . ModuleSettingsServiceInterface::STORE_ID                             => 'Store ID',
    'HELP_SHOP_MODULE_' . ModuleSettingsServiceInterface::STORE_ID                        => 'Your store ID (e.g. 10012345678) which is required for the basic authentication.',
    'SHOP_MODULE_' . ModuleSettingsServiceInterface::USER_ID                              => 'User ID',
    'HELP_SHOP_MODULE_' . ModuleSettingsServiceInterface::USER_ID                         => 'The user ID denoting the user who is allowed to access the Web Service API, e.g. 1. Again, this is required for the basic authentication.',
    'SHOP_MODULE_' . ModuleSettingsServiceInterface::BASIC_AUTH_PASSWORD                  => 'Basic Authentication Password',
    'HELP_SHOP_MODULE_' . ModuleSettingsServiceInterface::BASIC_AUTH_PASSWORD             => 'The password required for the basic authentication.',
    'SHOP_MODULE_' . ModuleSettingsServiceInterface::CLIENT_CERT_INSTALL_PASSWORD         => 'Client Certificate Installation Password',
    'HELP_SHOP_MODULE_' . ModuleSettingsServiceInterface::CLIENT_CERT_INSTALL_PASSWORD    => 'The password which is required to access the p12 file (containing the client certificate and private key file).',
    'SHOP_MODULE_' . ModuleSettingsServiceInterface::CLIENT_CERT_PRIVATEKEY_PASSWORD      => 'Client Certificate Private Key Password',
    'HELP_SHOP_MODULE_' . ModuleSettingsServiceInterface::CLIENT_CERT_PRIVATEKEY_PASSWORD => 'This password protects the private key of the client certificate. This password is needed to access the private key file (“Client Certificate Private Key”) It follows the naming scheme ckp_creationTimestamp. For instance, this might be ckp_1193927132.',
];
