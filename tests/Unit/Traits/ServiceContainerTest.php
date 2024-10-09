<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\IPG\API\Service\ServiceContainer;

use PHPUnit\Framework\TestCase;
use OxidSolutionCatalysts\TeleCash\Traits\ServiceContainer;
use Psr\Container\ContainerInterface;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use Psr\Container\NotFoundExceptionInterface;
use OxidSolutionCatalysts\TeleCash\Tests\Unit\IPG\API\Service\TestClassWithServiceContainer;
use ReflectionClass;

class ServiceContainerTest extends TestCase
{
    private $testClass;

    private $traitObject;

    protected function setUp(): void
    {
        $this->testClass = new TestClassWithServiceContainer();

        // Erstellen einer konkreten Klasse, die den Trait verwendet
        $this->traitObject = new class () {
            use ServiceContainer;
        };
    }

    public function testGetContainer()
    {
        $mockContainer = $this->createMock(ContainerInterface::class);
        $mockContainerFactory = $this->createMock(ContainerFactory::class);
        $mockContainerFactory->expects($this->once())
            ->method('getContainer')
            ->willReturn($mockContainer);

        $testClass = $this->getMockBuilder(TestClassWithServiceContainer::class)
            ->onlyMethods(['getContainerFactory'])
            ->getMock();
        $testClass->expects($this->once())
            ->method('getContainerFactory')
            ->willReturn($mockContainerFactory);

        $result = $testClass->getContainer();

        $this->assertSame($mockContainer, $result);
    }

    public function testGetServiceFromContainer()
    {
        $mockService = $this->createMock(\stdClass::class);
        $mockContainer = $this->createMock(ContainerInterface::class);
        $mockContainer->expects($this->once())
            ->method('get')
            ->with('TestService')
            ->willReturn($mockService);

        $mockContainerFactory = $this->createMock(ContainerFactory::class);
        $mockContainerFactory->expects($this->once())
            ->method('getContainer')
            ->willReturn($mockContainer);

        $testClass = $this->getMockBuilder(TestClassWithServiceContainer::class)
            ->onlyMethods(['getContainerFactory'])
            ->getMock();
        $testClass->expects($this->once())
            ->method('getContainerFactory')
            ->willReturn($mockContainerFactory);

        $result = $testClass->getServiceFromContainer('TestService');

        $this->assertSame($mockService, $result);
    }

    public function testGetServiceFromContainerThrowsException()
    {
        $mockContainer = $this->createMock(ContainerInterface::class);
        $mockContainer->expects($this->once())
            ->method('get')
            ->with('NonExistentService')
            ->willThrowException($this->createMock(NotFoundExceptionInterface::class));

        $mockContainerFactory = $this->createMock(ContainerFactory::class);
        $mockContainerFactory->expects($this->once())
            ->method('getContainer')
            ->willReturn($mockContainer);

        $testClass = $this->getMockBuilder(TestClassWithServiceContainer::class)
            ->onlyMethods(['getContainerFactory'])
            ->getMock();
        $testClass->expects($this->once())
            ->method('getContainerFactory')
            ->willReturn($mockContainerFactory);

        $this->expectException(NotFoundExceptionInterface::class);

        $testClass->getServiceFromContainer('NonExistentService');
    }

    public function testGetContainerFactory()
    {
        // Reflection verwenden, um auf die geschützte Methode zuzugreifen
        $reflection = new ReflectionClass($this->traitObject);
        $method = $reflection->getMethod('getContainerFactory');
        $method->setAccessible(true);

        // Die Methode aufrufen
        $result = $method->invoke($this->traitObject);

        // Überprüfen, ob das Ergebnis eine Instanz von ContainerFactory ist
        $this->assertInstanceOf(ContainerFactory::class, $result);

        // Überprüfen, ob es die Singleton-Instanz ist
        $this->assertSame(ContainerFactory::getInstance(), $result);
    }
}
