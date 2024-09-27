<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\TeleCash\Core\Service;

/**
 * use this service as an alternative to oxid core oxNew, for writing more readable code and better unit tests
 */
class OxNewService
{
    /**
     * @template T of object
     * @param class-string<T> $fqcn
     * @param array<int|string, mixed> $constructorArgs
     * @return T
     */
    public function oxNew(string $fqcn, array $constructorArgs = []): object
    {
        return oxNew($fqcn, ...$constructorArgs);
    }
}
