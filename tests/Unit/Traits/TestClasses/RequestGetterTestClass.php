<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\Traits\TestClasses;

use OxidSolutionCatalysts\TeleCash\Core\Service\RegistryService;
use OxidSolutionCatalysts\TeleCash\Traits\RequestGetter;

class RequestGetterTestClass
{
    use RequestGetter;

    private ?RegistryService $registryService = null;

    public function getServiceFromContainer(string $serviceName)
    {
        return $this->registryService;
    }

    public function setRegistryService(RegistryService $service): void
    {
        $this->registryService = $service;
    }
}
