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
    'SHOP_MODULE_' . ModuleFileSettingsServiceInterface::CLIENT_CERT_P12_FILE             => 'Client-Zertifikat p12-Datei',
    'HELP_SHOP_MODULE_' . ModuleFileSettingsServiceInterface::CLIENT_CERT_P12_FILE        => 'Das Client-Zertifikat und der private Schlüssel, gespeichert in einer p12-Datei mit dem Benennungsschema WSshopID._.benutzerID.p12. Zum Beispiel wäre dies bei den oben genannten Shop-ID / Benutzer-ID-Beispielen WS101._.007.p12. Diese Datei wird zur Authentifizierung des Clients am Gateway verwendet.',
    'SHOP_MODULE_' . ModuleFileSettingsServiceInterface::CLIENT_CERT_PRIVATEKEY_FILE      => 'Privater Schlüssel des Client-Zertifikats',
    'HELP_SHOP_MODULE_' . ModuleFileSettingsServiceInterface::CLIENT_CERT_PRIVATEKEY_FILE => 'Der private Schlüssel des Client-Zertifikats, gespeichert in einer Schlüsseldatei mit dem Benennungsschema WSshopID._.benutzerID.key. Zum Beispiel wäre dies bei den oben genannten Shop-ID / Benutzer-ID-Beispielen WS10012345678._.1.key.',
    'SHOP_MODULE_' . ModuleFileSettingsServiceInterface::CLIENT_CERT_PEM_FILE             => 'Client-Zertifikat PEM-Datei',
    'HELP_SHOP_MODULE_' . ModuleFileSettingsServiceInterface::CLIENT_CERT_PEM_FILE        => 'Die Liste der Client-Zertifikate, gespeichert in einer PEM-Datei mit dem Benennungsschema WSshopID._.benutzerID.pem. Zum Beispiel wäre dies bei den oben genannten Shop-ID / Benutzer-ID-Beispielen WS10012345678._.1.pem.',
    'SHOP_MODULE_' . ModuleFileSettingsServiceInterface::TRUST_ANCHOR_PEM_FILE            => 'Vertrauensanker',
    'HELP_SHOP_MODULE_' . ModuleFileSettingsServiceInterface::TRUST_ANCHOR_PEM_FILE       => 'als verkettete PEM-Datei (tlstrust.pem): Die Datei enthält eine Liste von Client-Zertifikaten, denen Sie vertrauen sollten, um eine vertrauenswürdige Verbindung zur laufenden Web Service API herzustellen.',

    'TELECASH_FILE_UPLOAD_SUCCESSFUL' => 'Datei %s erfolgreich hochgeladen',
    'TELECASH_FILE_UPLOAD_ERROR'      => 'Fehler beim Hochladen: %s',
    'TELECASH_FILE_UPLOAD_NOTVALID'   => 'Datei %s ist nicht valide',
];
