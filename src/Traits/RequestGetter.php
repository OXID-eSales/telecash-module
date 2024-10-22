<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Traits;

use OxidEsales\Eshop\Core\Request;
use OxidSolutionCatalysts\TeleCash\Core\Service\RegistryService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Convenience trait to work with Request-Data
 */
trait RequestGetter
{
    use ServiceContainer;

    protected ?Request $request;

    /**
     * return a requested Integer
     *
     * @param string $key
     * @param int $default
     * @return int
     */
    public function getIntegerRequestData(string $key, int $default = 0): int
    {
        /** @var string $oxidDefault */
        $oxidDefault = $default;
        $value = $this->getRequest() ? $this->getRequest()->getRequestParameter($key, $oxidDefault) : $default;
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
        /** @var string $oxidDefault */
        $oxidDefault = $default;
        $value = $this->getRequest() ? $this->getRequest()->getRequestParameter($key, $oxidDefault) : $default;
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
        $value = $this->getRequest() ? $this->getRequest()->getRequestParameter($key) : false;
        return (bool)$value;
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
        $value = $this->getRequest() ? $this->getRequest()->getRequestEscapedParameter($key, $default) : $default;
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

    /**
     * Helper Method to get the Request-Object
     * @return null|Request
     */
    private function getRequest(): ?Request
    {
        if (is_null($this->request)) {
            try {
                $this->request = $this->getServiceFromContainer(RegistryService::class)->getRequest();
            } catch (NotFoundExceptionInterface | ContainerExceptionInterface) {
                // do noting
            }
        }
        return $this->request;
    }
}
