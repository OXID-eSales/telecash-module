<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Traits;

use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use Psr\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Convenience trait to fetch services from DI container.
 * Use for example in classes where it's not possible to inject services in
 * the constructor because constructor is inherited from a shop core class.
 */
trait ServiceContainer
{
    protected ?ContainerInterface $container = null;

    /**
     * @template T
     * @psalm-param class-string<T> $serviceName
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     *
     * @return T
     */
    protected function getServiceFromContainer(string $serviceName)
    {
        if ($this->container === null) {
            $this->container = $this->getContainer();
        }
        return $this->container->get($serviceName);
    }

    protected function getContainer(): ContainerInterface
    {
        return $this->getContainerFactory()->getContainer();
    }

    protected function getContainerFactory(): ContainerFactory
    {
        return ContainerFactory::getInstance();
    }
}
