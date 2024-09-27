<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Settings\Service;

interface ModuleSettingsServiceInterface
{
    public const API_MODE = 'osctelecash_apimode';
    public const API_MODE_LIVE = 'live';
    public const API_MODE_SANDBOX = 'sandbox';

    public const STORE_ID = 'osctelecash_storeid';

    public const USER_ID = 'osctelecash_userid';

    public const BASIC_AUTH_PASSWORD = 'osctelecash_basicauthpassword';

    public const CLIENT_CERT_INSTALL_PASSWORD = 'osctelecash_certificateinstallationpassword';

    public const CLIENT_CERT_PRIVATEKEY_PASSWORD = 'osctelecash_clientcertificateprivatekeypassword';

    public function isLiveApiMode(): bool;

    public function getApiMode(): string;

    public function saveApiMode(string $value): void;

    public function getStoreId(): string;

    public function saveStoreId(string $value): void;

    public function getUserId(): string;

    public function saveUserId(string $value): void;

    public function getBasicAuthPassword(): string;

    public function saveBasicAuthPassword(string $value): void;

    public function getClientCertificateInstallationPassword(): string;

    public function saveClientCertificateInstallationPassword(string $value): void;

    public function getClientCertificatePrivateKeyPassword(): string;

    public function saveClientCertificatePrivateKeyPassword(string $value): void;
}
