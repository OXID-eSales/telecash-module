<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Traits;

use OxidEsales\Eshop\Core\Request;
use OxidSolutionCatalysts\TeleCash\Core\Service\RegistryService;

/**
 * Convenience trait to work with Request-Data
 */
trait RequestGetter
{
    private ?Request $request = null;

    /**
     * Helper Method to get the Request-Object
     * Initializes the request object if not already set
     *
     * @return Request|null
     */
    private function getRequest(): ?Request
    {
        if ($this->request === null) {
            $registryService = $this->getServiceFromContainer(RegistryService::class);
            if ($registryService instanceof RegistryService) {
                $this->request = $registryService->getRequest();
            }
        }
        return $this->request;
    }

    /**
     * return a requested Integer
     *
     * @param string $key
     * @param int $default
     * @return int
     */
    public function getIntegerRequestData(string $key, int $default = 0): int
    {
        $request = $this->getRequest();
        if (!$request instanceof Request) {
            return 0;
        }

        /** @var string $oxidDefault */
        $oxidDefault = $default;
        $value = $request->getRequestParameter($key, $oxidDefault);
        return is_int($value) ? $value : $default;
    }

    /**
     * return a requested array
     *
     * @param string $key
     * @param array<string|int, mixed> $default
     * @return array<string|int, mixed>
     */
    public function getArrayRequestData(string $key, array $default = []): array
    {
        $request = $this->getRequest();
        if (!$request instanceof Request) {
            return [];
        }

        /** @var string $oxidDefault */
        $oxidDefault = $default;
        $value = $request->getRequestParameter($key, $oxidDefault);
        return is_array($value) ? $value : $default;
    }

    /**
     * return a requested boolean
     *
     * @param string $key
     * @return bool
     */
    public function getBoolRequestData(string $key): bool
    {
        $request = $this->getRequest();
        if (!$request instanceof Request) {
            return false;
        }

        return (bool)$request->getRequestParameter($key);
    }

    /**
     * return a requested and escaped string
     *
     * @param string $key
     * @param string $default
     * @return string
     */
    public function getStringRequestEscapedData(string $key, string $default = ''): string
    {
        $request = $this->getRequest();
        if (!$request instanceof Request) {
            return $default;
        }

        $value = $request->getRequestEscapedParameter($key, $default);
        return is_string($value) ? $value : $default;
    }

    /**
     * return a requested and escaped array
     *
     * @param string $key
     * @param array<string|int, mixed> $default
     * @return array<string|int, mixed>
     */
    public function getArrayRequestEscapedData(string $key, array $default = []): array
    {
        /** @var string $oxidDefault */
        $oxidDefault = $default;
        $value = $this->getRequest() ? $this->getRequest()->getRequestParameter($key, $oxidDefault) : $default;
        return is_array($value) ? $value : $default;
    }
}
