<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleFileSettingsServiceInterface;
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
    'SHOP_MODULE_' . ModuleFileSettingsServiceInterface::CLIENT_CERT_P12_FILE             => 'Client Certificate p12 File',
    'HELP_SHOP_MODULE_' . ModuleFileSettingsServiceInterface::CLIENT_CERT_P12_FILE        => 'The client certificate and private key stored in a p12 file having the naming scheme WSstoreID._.userID.p12, e.g. in case of the above store ID / user ID examples, this would be WS101._.007.p12. This file is used for authenticating the client at the Gateway.',
    'SHOP_MODULE_' . ModuleFileSettingsServiceInterface::CLIENT_CERT_PRIVATEKEY_FILE      => 'Client Certificate Private Key',
    'HELP_SHOP_MODULE_' . ModuleFileSettingsServiceInterface::CLIENT_CERT_PRIVATEKEY_FILE => 'The private key of the client certificate stored in a key file having the naming scheme WSstoreID._.userID.key, e.g. in case of the above store ID / user ID examples, this would be WS10012345678._.1.key.',
    'SHOP_MODULE_' . ModuleFileSettingsServiceInterface::TRUST_ANCHOR_PEM_FILE            => 'Trust Anchor',
    'HELP_SHOP_MODULE_' . ModuleFileSettingsServiceInterface::TRUST_ANCHOR_PEM_FILE       => 'as concatenated PEM File (tlstrust.pem): The file contains a list of client certificates you should trust to establish a trusted connection to the running the Web Service API. ',

    'TELECASH_FILE_UPLOAD_SUCCESSFUL' => 'File %s uploaded successfully',
    'TELECASH_FILE_UPLOAD_ERROR'      => 'Error uploading: %s',
    'TELECASH_FILE_UPLOAD_NOTVALID'   => 'File %s is not valid',
    'TELECASH_DELETE'                 => 'Delete File?',
];
