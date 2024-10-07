<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\TeleCash\Core\Service;

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
        if (function_exists('oxNew')) {
            return oxNew($fqcn, ...$constructorArgs);
        }

        return new $fqcn(...$constructorArgs);
    }
}
