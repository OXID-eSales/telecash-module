<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Settings\Service;

interface ModuleSettingsServiceInterface
{
    public const MODULE_CONFIG_API_VARGROUP = 'osctelecash_api';
    public const MODULE_CONFIG_API_FRONTEND_VARGROUP = 'osctelecash_api_frontend';
    public const MODULE_CONFIG_API_BACKEND_VARGROUP = 'osctelecash_api_backend';
    public const MODULE_CONFIG_DEBUG_VARGROUP = 'osctelecash_debug';
    public const MODULE_CONFIG_LANGUAGE = 'osctelecash_language';

    public const API_MODE = 'osctelecash_apimode';
    public const API_MODE_LIVE = 'live';
    public const API_MODE_SANDBOX = 'sandbox';

    /**
     * possible API-Modes
     */
    public const API_MODE_VALUES = [
        self::API_MODE_LIVE,
        self::API_MODE_SANDBOX,
    ];

    /**
     * possible API-Modes
     */
    public const CONNECT_URLS = [
        self::API_MODE_LIVE    => 'https://www.ipg-online.com/connect/gateway/processing',
        self::API_MODE_SANDBOX => 'https://test.ipg-online.com/connect/gateway/processing',
    ];

    public const SERVICE_URLS = [
        self::API_MODE_LIVE    => 'https://www.ipg-online.com/ipgapi/services',
        self::API_MODE_SANDBOX => 'https://test.ipg-online.com/ipgapi/services',
    ];

    public const STORE_ID = 'osctelecash_storeid';

    public const SHARED_SECRET = 'osctelecash_shared_secret';

    public const USER_ID = 'osctelecash_userid';

    public const BASIC_AUTH_PASSWORD = 'osctelecash_basicauthpassword';

    public const CLIENT_CERT_INSTALL_PASSWORD = 'osctelecash_certificateinstallationpassword';

    public const CLIENT_CERT_PRIVATEKEY_PASSWORD = 'osctelecash_clientcertificateprivatekeypassword';

    public const LOG_LEVEL = 'osctelecash_loglevel';
    public const LOG_LEVEL_ERROR = 'error';
    public const LOG_LEVEL_INFO = 'info';
    public const LOG_LEVEL_DEBUG = 'debug';

    /**
     * Mapping of log level names to their numeric priorities
     * Lower numbers indicate more detailed logging
     *
     * @var array<string, int>
     */
    public const TELECASH_LOG_LEVELS = [
        self::LOG_LEVEL_ERROR => 400,
        self::LOG_LEVEL_INFO  => 200,
        self::LOG_LEVEL_DEBUG => 100
    ];

    public function isValidBackendConfiguration(): bool;

    public function isValidFrontendConfiguration(): bool;

    public function isLiveApiMode(): bool;

    public function getApiMode(): string;

    public function saveApiMode(string $value): void;

    public function getConnectUrl(): string;

    public function getServiceUrl(): string;

    public function getStoreId(): string;

    public function saveStoreId(string $value): void;

    public function getSharedSecret(): string;

    public function saveSharedSecret(string $value): void;

    public function getUserId(): string;

    public function saveUserId(string $value): void;

    public function getBasicAuthPassword(): string;

    public function saveBasicAuthPassword(string $value): void;

    public function getClientCertificateInstallationPassword(): string;

    public function saveClientCertificateInstallationPassword(string $value): void;

    public function getClientCertificatePrivateKeyPassword(): string;

    public function saveClientCertificatePrivateKeyPassword(string $value): void;

    public function getLogLevel(): string;

    public function saveLogLevel(string $value): void;
}
