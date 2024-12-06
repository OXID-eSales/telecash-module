<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Settings\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidSolutionCatalysts\TeleCash\Core\Module;

/**
 * @extendable-class
 */
class ModuleSettingsService implements ModuleSettingsServiceInterface
{
    public function __construct(
        private readonly ModuleSettingServiceInterface $moduleSettingService,
        private readonly ModuleFileSettingsServiceInterface $moduleFileSettingsService
    ) {
    }

    /**
     * if true is it a complete backend configuration
     */
    public function isValidBackendConfiguration(): bool
    {
        return $this->getStoreId()
            && $this->getBasicAuthPassword()
            && $this->getClientCertificateInstallationPassword()
            && $this->getClientCertificatePrivateKeyPassword()
            && $this->moduleFileSettingsService->checkClientCertificateP12FileExists()
            && $this->moduleFileSettingsService->checkClientCertificatePrivateKeyFileExists()
            && $this->moduleFileSettingsService->checkTrustAnchorPEMFileExists();
    }

    /**
     * if true is it a complete frontend configuration
     */
    public function isValidFrontendConfiguration(): bool
    {
        return $this->getStoreId()
            && $this->getSharedSecret();
    }

    /**
     * if true it runs in Live-Mode, if false it runs in Sandbox-Mode
     */
    public function isLiveApiMode(): bool
    {
        return self::API_MODE_LIVE === $this->getApiMode();
    }

    /**
     * get the actual saved TeleCash API Mode
     */
    public function getApiMode(): string
    {
        $value = (string)$this->moduleSettingService->getString(self::API_MODE, Module::MODULE_ID);

        return (!empty($value) && in_array($value, self::API_MODE_VALUES, true)) ? $value : self::API_MODE_LIVE;
    }

    /**
     * save the TeleCash API Mode
     */
    public function saveApiMode(string $value): void
    {
        $this->moduleSettingService->saveString(self::API_MODE, $value, Module::MODULE_ID);
    }

    /**
     * get the Connect URL by API-Mode
     */
    public function getConnectUrl(): string
    {
        return self::CONNECT_URLS[$this->getApiMode()];
    }

    /**
     * get the Service URL by API-Mode
     */
    public function getServiceUrl(): string
    {
        return self::SERVICE_URLS[$this->getApiMode()];
    }

    /**
     * get the Store ID from Config
     * @return string
     */
    public function getStoreId(): string
    {
        return (string)$this->moduleSettingService->getString(self::STORE_ID, Module::MODULE_ID);
    }

    /**
     * save the Store ID to Config
     * @param string $value
     * @return void
     */
    public function saveStoreId(string $value): void
    {
        $this->moduleSettingService->saveString(self::STORE_ID, $value, Module::MODULE_ID);
    }

    /**
     * get the Shared Secret from Config
     * @return string
     */
    public function getSharedSecret(): string
    {
        return (string)$this->moduleSettingService->getString(self::SHARED_SECRET, Module::MODULE_ID);
    }

    /**
     * save the Shared Secret to Config
     * @param string $value
     * @return void
     */
    public function saveSharedSecret(string $value): void
    {
        $this->moduleSettingService->saveString(self::SHARED_SECRET, $value, Module::MODULE_ID);
    }

    /**
     * get the User ID from Config
     * @return string
     */
    public function getUserId(): string
    {
        return (string)$this->moduleSettingService->getString(self::USER_ID, Module::MODULE_ID);
    }

    /**
     * save the User ID to Config
     * @param string $value
     * @return void
     */
    public function saveUserId(string $value): void
    {
        $this->moduleSettingService->saveString(self::USER_ID, $value, Module::MODULE_ID);
    }

    /**
     * @return string
     */
    public function getBasicAuthPassword(): string
    {
        return (string)$this->moduleSettingService->getString(self::BASIC_AUTH_PASSWORD, Module::MODULE_ID);
    }

    /**
     * save the Basic Authentication Password to Config
     * @param string $value
     * @return void
     */
    public function saveBasicAuthPassword(string $value): void
    {
        $this->moduleSettingService->saveString(self::BASIC_AUTH_PASSWORD, $value, Module::MODULE_ID);
    }

    /**
     * @return string
     */
    public function getClientCertificateInstallationPassword(): string
    {
        return (string)$this->moduleSettingService->getString(self::CLIENT_CERT_INSTALL_PASSWORD, Module::MODULE_ID);
    }

    /**
     * save the Client Certificate Installation Password to Config
     * @param string $value
     * @return void
     */
    public function saveClientCertificateInstallationPassword(string $value): void
    {
        $this->moduleSettingService->saveString(self::CLIENT_CERT_INSTALL_PASSWORD, $value, Module::MODULE_ID);
    }

    /**
     * @return string
     */
    public function getClientCertificatePrivateKeyPassword(): string
    {
        return (string)$this->moduleSettingService->getString(self::CLIENT_CERT_PRIVATEKEY_PASSWORD, Module::MODULE_ID);
    }

    /**
     * save the Client Certificate Private Key Password to Config
     * @param string $value
     * @return void
     */
    public function saveClientCertificatePrivateKeyPassword(string $value): void
    {
        $this->moduleSettingService->saveString(self::CLIENT_CERT_PRIVATEKEY_PASSWORD, $value, Module::MODULE_ID);
    }

    /**
     * get the actual saved TeleCash LogLevel
     */
    public function getLogLevel(): string
    {
        $value = (string)$this->moduleSettingService->getString(self::LOG_LEVEL, Module::MODULE_ID);

        return (!empty($value) && array_key_exists($value, self::TELECASH_LOG_LEVELS)) ? $value : self::LOG_LEVEL_ERROR;
    }

    /**
     * save the TeleCash LogLevel
     */
    public function saveLogLevel(string $value): void
    {
        $this->moduleSettingService->saveString(self::LOG_LEVEL, $value, Module::MODULE_ID);
    }
}
