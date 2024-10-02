<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\TeleCash\Core\Service;

use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Language;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\UtilsView;

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

    /**
     * for better testing and to avoid "'static' methods cannot be mocked"
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function getLang(): Language
    {
        return Registry::getLang();
    }

    /**
     * for better testing and to avoid "'static' methods cannot be mocked"
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function getRequest(): Request
    {
        return Registry::getRequest();
    }

    /**
     * for better testing and to avoid "'static' methods cannot be mocked"
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function getUtilsView(): UtilsView
    {
        return Registry::getUtilsView();
    }
}
