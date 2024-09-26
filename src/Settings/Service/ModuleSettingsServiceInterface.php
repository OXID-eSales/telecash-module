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

    public function isLiveApiMode(): bool;

    public function getApiMode(): string;

    public function saveApiMode(string $value): void;
}
