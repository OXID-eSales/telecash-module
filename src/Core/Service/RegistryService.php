<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\TeleCash\Core\Service;

use OxidEsales\Eshop\Core\Registry;

class RegistryService
{
    public function getConfig()
    {
        return Registry::getConfig();
    }
}