<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\Traits\TestClasses;

use OxidSolutionCatalysts\TeleCash\Core\Service\RegistryService;
use OxidSolutionCatalysts\TeleCash\Traits\Json;

class JsonTestClass
{
    use Json;

    private ?RegistryService $registryService = null;

    public function setRegistryService(RegistryService $service): void
    {
        $this->registryService = $service;
    }

    // Make protected methods public for testing
    public function getJsonPostDataTest(): string
    {
        return $this->getJsonPostData();
    }

    public function jsonToArrayTest(string $json): array
    {
        return $this->jsonToArray($json);
    }

    public function arrayToJsonTest(array $data): string
    {
        return $this->arrayToJson($data);
    }

    public function outputJsonTest(string $json): void
    {
        $this->outputJson($json);
    }
}
