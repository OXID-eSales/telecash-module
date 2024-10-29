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
use RuntimeException;

/**
 * Convenience trait to fetch services from DI container.
 * Use for example in classes where it's not possible to inject services in
 * the constructor because constructor is inherited from a shop core class.
 */
trait ServiceContainer
{
    protected ?ContainerInterface $container = null;

    /**
     * Gets a required service from the container
     *
     * @template T of object
     * @param class-string<T> $serviceClass
     * @param string $serviceName
     * @return T
     * @throws RuntimeException If the service cannot be retrieved
     */
    private function getRequiredService(string $serviceClass, string $serviceName): object
    {
        $service = $this->getServiceFromContainer($serviceClass);
        if (!$service instanceof $serviceClass) {
            throw new RuntimeException(sprintf('Could not get %s', $serviceName));
        }
        return $service;
    }

    /**
     * @template T of object
     * @param class-string<T> $serviceId
     * @return T|null
     */
    protected function getServiceFromContainer(string $serviceId): ?object
    {
        try {
            if ($this->container && $this->container->has($serviceId)) {
                /** @var T */
                return $this->container->get($serviceId);
            }
        } catch (ContainerExceptionInterface | NotFoundExceptionInterface) {
            // Service not available
        }

        return null;
    }

    /**
     * @param ContainerInterface $container
     * @return void
     */
    protected function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
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
