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

    'SHOP_MODULE_' . ModuleSettingsServiceInterface::STORE_ID                             => 'Shop-ID',
    'HELP_SHOP_MODULE_' . ModuleSettingsServiceInterface::STORE_ID                        => 'Ihre Shop-ID (z.B. 10012345678), die für die Basis-Authentifizierung erforderlich ist.',
    'SHOP_MODULE_' . ModuleSettingsServiceInterface::USER_ID                              => 'Benutzer-ID',
    'HELP_SHOP_MODULE_' . ModuleSettingsServiceInterface::USER_ID                         => 'Die Benutzer-ID, die den Benutzer bezeichnet, der auf die Web-Service-API zugreifen darf, z.B. 1. Auch dies ist für die Basis-Authentifizierung erforderlich.',
    'SHOP_MODULE_' . ModuleSettingsServiceInterface::BASIC_AUTH_PASSWORD                  => 'Passwort für Basis-Authentifizierung',
    'HELP_SHOP_MODULE_' . ModuleSettingsServiceInterface::BASIC_AUTH_PASSWORD             => 'Das Passwort, das für die Basis-Authentifizierung erforderlich ist.',
    'SHOP_MODULE_' . ModuleSettingsServiceInterface::CLIENT_CERT_INSTALL_PASSWORD         => 'Installationspasswort für Client-Zertifikat',
    'HELP_SHOP_MODULE_' . ModuleSettingsServiceInterface::CLIENT_CERT_INSTALL_PASSWORD    => 'Das Passwort, das erforderlich ist, um auf die p12-Datei (die das Client-Zertifikat und die private Schlüsseldatei enthält) zuzugreifen.',
    'SHOP_MODULE_' . ModuleSettingsServiceInterface::CLIENT_CERT_PRIVATEKEY_PASSWORD      => 'Passwort für den privaten Schlüssel des Client-Zertifikats',
    'HELP_SHOP_MODULE_' . ModuleSettingsServiceInterface::CLIENT_CERT_PRIVATEKEY_PASSWORD => 'Dieses Passwort schützt den privaten Schlüssel des Client-Zertifikats. Es wird benötigt, um auf die private Schlüsseldatei ("Privater Schlüssel des Client-Zertifikats") zuzugreifen. Es folgt dem Benennungsschema ckp_Erstellungszeitstempel. Zum Beispiel könnte dies ckp_1193927132 sein.',
];
