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
    public const API_MODE_VALUES = [
        self::API_MODE_LIVE,
        self::API_MODE_SANDBOX,
    ];

    public function __construct(
        private ModuleSettingServiceInterface $moduleSettingService
    ) {
    }

    public function isLiveApiMode(): bool
    {
        return self::API_MODE_SANDBOX === $this->getApiMode();
    }

    public function getApiMode(): string
    {
        $value = (string)$this->moduleSettingService->getString(self::API_MODE, Module::MODULE_ID);

        return (!empty($value) && in_array($value, self::API_MODE_VALUES, true)) ? $value : self::API_MODE_LIVE;
    }

    public function saveApiMode(string $value): void
    {
        $this->moduleSettingService->saveString(self::API_MODE, $value, Module::MODULE_ID);
    }
}
