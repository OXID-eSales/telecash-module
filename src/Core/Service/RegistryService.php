<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\TeleCash\Core\Service;

use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Registry;

class RegistryService
{
    /**
     * for better testing and to avoid "'static' methods cannot be mocked"
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function getConfig(): Config
    {
        return Registry::getConfig();
    }
}
