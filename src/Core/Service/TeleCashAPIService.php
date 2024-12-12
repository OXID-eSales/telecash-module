<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Core\Service;

use OxidSolutionCatalysts\TeleCash\IPG\TeleCash;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleFileSettingsServiceInterface;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleSettingsServiceInterface;

/**
 * Service for creating and configuring TeleCash API instances.
 * Handles the API configuration and initialization using module settings.
 */
class TeleCashAPIService implements TeleCashAPIServiceInterface
{
    private ModuleSettingsServiceInterface $moduleSettings;
    private ModuleFileSettingsServiceInterface $moduleFileSettings;
    private Logger $logger;

    public function __construct(
        ModuleSettingsServiceInterface $moduleSettings,
        ModuleFileSettingsServiceInterface $moduleFileSettings,
        Logger $logger
    ) {
        $this->moduleSettings = $moduleSettings;
        $this->moduleFileSettings = $moduleFileSettings;
        $this->logger = $logger;
    }

    /**
     * Creates and returns a configured TeleCash API instance.
     * Uses module settings to configure the API with necessary credentials and endpoints.
     *
     * @return TeleCash A configured TeleCash API instance
     */
    public function getTeleCashAPI(): TeleCash
    {
        $clientCert = $this->moduleFileSettings->getClientCertificateP12FilePath();
        $clientKey = $this->moduleFileSettings->getClientCertificatePrivateKeyFilePath();
        $caInfo = $this->moduleFileSettings->getTrustAnchorPEMFilePath();
        $apiUser = $this->moduleSettings->getUserId();
        $apiPass = $this->moduleSettings->getBasicAuthPassword();
        $clientKeyPass = $this->moduleSettings->getClientCertificateInstallationPassword();
        $serviceUrl = $this->moduleSettings->getServiceUrl();

        return new TeleCash(
            $serviceUrl,
            $apiUser,
            $apiPass,
            $clientCert,
            $clientKey,
            $clientKeyPass,
            $caInfo,
            $this->logger
        );
    }
}
