<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\IPG\API\Service;

use OxidSolutionCatalysts\TeleCash\Traits\ServiceContainer;
use Psr\Container\ContainerInterface;

class TestClassWithServiceContainer
{
    use ServiceContainer {
        getContainer as public;
        getServiceFromContainer as public;
        getContainerFactory as public;
    }

    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }
}
