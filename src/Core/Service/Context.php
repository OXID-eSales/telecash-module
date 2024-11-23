<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Core\Service;

use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\EshopCommunity\Application\Model\Basket;
use OxidSolutionCatalysts\TeleCash\Core\Module;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleSettingsService;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleSettingsServiceInterface;
use Symfony\Component\Filesystem\Path;

/**
 * Context Service for TeleCash Module
 *
 * Provides context-specific functionality for the TeleCash module,
 * particularly focused on file system operations and path management.
 * Handles the configuration and creation of log file paths.
 *
 * Features:
 * - Dynamic log file path generation
 * - Integration with shop configuration
 * - Date-based log file naming
 * - Symfony Path component usage for cross-platform compatibility
 */
class Context
{
    private Session $session;

    /**
     * Initializes the context service with required dependencies
     *
     * @param Config $shopConfig Shop configuration instance providing access to shop settings
     * @param RegistryService $registryService
     * @param ModuleSettingsServiceInterface $moduleSettings
     */
    public function __construct(
        private readonly Config $shopConfig,
        private readonly RegistryService $registryService,
        private readonly ModuleSettingsServiceInterface $moduleSettings
    ) {
        $this->session = $this->registryService->getSession();
    }

    /**
     * Generates the full path for the TeleCash log file
     *
     * Creates a path by combining:
     * - Shop's log directory (from configuration)
     * - TeleCash-specific subdirectory
     * - Date-based log filename
     *
     * Uses Symfony's Path component to ensure cross-platform compatibility
     * of the generated paths.
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @return string Absolute path to the log file
     */
    public function getTeleCashLogFilePath(): string
    {
        return Path::join(
            $this->shopConfig->getLogsDir(),
            Module::MODULE_ID,
            $this->getTeleCashLogFileName()
        );
    }

    /**
     * Generates the log filename based on current date
     *
     * Creates a log filename in the format:
     * osc_telecash_YYYY-MM-DD.log
     *
     * @return string Generated log filename
     */
    private function getTeleCashLogFileName(): string
    {
        return Module::MODULE_ID . "_" . $this->getCurrentDate() . ".log";
    }

    /**
     * Gets current date in Y-m-d format
     * Protected to allow overriding in tests
     */
    protected function getCurrentDate(): string
    {
        return date('Y-m-d');
    }

    /**
     * Get Success-URL for TeleCash-Connect
     */
    public function getSuccessUrl(Basket $basket, string $deliveryAddressMD5 = ''): string
    {
        $parameter = [
            "cl"     => "order",
            "fnc"    => "execute",
            "stoken" => $this->session->getSessionChallengeToken()
        ];

        if ($this->shopConfig->getConfigParam('blConfirmAGB')) {
            $parameter["ord_agb"] = 1;
        }

        if ($deliveryAddressMD5) {
            $parameter["sDeliveryAddressMD5"] = $deliveryAddressMD5;
        }

        if ($this->shopConfig->getConfigParam('blEnableIntangibleProdAgreement')) {
            if ($basket->hasArticlesWithDownloadableAgreement()) {
                $parameter["oxdownloadableproductsagreement"] = "1";
            }
            if ($basket->hasArticlesWithIntangibleAgreement()) {
                $parameter["oxserviceproductsagreement"] = "1";
            }
        }

        return $this->prepareUrl($parameter);
    }

    /**
     * Get Fail-URL for TeleCash-Connect
     */
    public function getFailUrl(): string
    {
        $parameter = [
            "cl"           => "payment",
            "fnc"          => "showTeleCashError",
        ];

        return $this->prepareUrl($parameter);
    }

    /**
     * Get Notification-URL for TeleCash-Connect
     */
    public function getNotificationUrl(): string
    {
        $parameter = [
            "cl"  => "FrontendTeleCashNotificationEndpoint",
            "fnc" => "receiveNotifications",
        ];

        return $this->prepareUrl($parameter);
    }

    /**
     * Helper for Url-Methods
     * @param array<string, int|string> $parameter
     * @return string
     */
    private function prepareUrl(array $parameter): string
    {
        // add xdebug in sandbox for better testing
        $sandboxMode = !$this->moduleSettings->isLiveApiMode();
        if ($sandboxMode) {
            $parameter['XDEBUG_SESSION_START'] = "1";
        }

        return html_entity_decode(
            $this->shopConfig->getCurrentShopUrl(false) . 'index.php?' . http_build_query($parameter)
        );
    }
}
