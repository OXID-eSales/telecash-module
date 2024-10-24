<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Traits;

use PHPUnit\Framework\TestCase;
use OxidSolutionCatalysts\TeleCash\Traits\ServiceContainer;
use Psr\Container\ContainerInterface;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use Psr\Container\NotFoundExceptionInterface;
use OxidSolutionCatalysts\TeleCash\Tests\Unit\IPG\API\Service\TestClassWithServiceContainer;
use ReflectionClass;

/**
 * Test Suite for the ServiceContainer trait
 *
 * This test class verifies the functionality of the ServiceContainer trait,
 * which provides convenient access to services through dependency injection.
 * The trait is typically used in classes where constructor injection is not
 * possible due to inheritance constraints from shop core classes.
 *
 * Key aspects tested:
 * - Container retrieval and initialization
 * - Service retrieval from container
 * - Error handling for missing services
 * - Container factory functionality
 */
class ServiceContainerTest extends TestCase
{
    private $traitObject;

    /**
     * Set up the test environment before each test
     *
     * Creates instances of:
     * - A test class that uses the ServiceContainer trait
     * - A anonymous class implementing the trait for direct trait testing
     */
    protected function setUp(): void
    {
        // Create anonymous class to test the trait directly
        $this->traitObject = new class () {
            use ServiceContainer;
        };
    }

    /**
     * Tests the retrieval of the container instance
     *
     * Verifies that:
     * - The container factory is called correctly
     * - The container is retrieved from the factory
     * - The correct container instance is returned
     */
    public function testGetContainer(): void
    {
        // Create mock objects for container and factory
        $mockContainer = $this->createMock(ContainerInterface::class);
        $mockContainerFactory = $this->createMock(ContainerFactory::class);

        // Configure factory to return our mock container
        $mockContainerFactory->expects($this->once())
            ->method('getContainer')
            ->willReturn($mockContainer);

        // Create test class with mocked factory method
        $testClass = $this->getMockBuilder(TestClassWithServiceContainer::class)
            ->onlyMethods(['getContainerFactory'])
            ->getMock();
        $testClass->expects($this->once())
            ->method('getContainerFactory')
            ->willReturn($mockContainerFactory);

        $result = $testClass->getContainer();

        // Verify the correct container is returned
        $this->assertSame($mockContainer, $result);
    }

    /**
     * Tests successful service retrieval from the container
     *
     * This test verifies that:
     * - The container correctly checks for service availability
     * - The service is retrieved when available
     * - The correct service instance is returned
     */
    public function testGetServiceFromContainerSuccess(): void
    {
        // Create a mock service for testing
        $mockService = $this->createMock(\stdClass::class);

        // Configure container mock with expected behavior
        $mockContainer = $this->createMock(ContainerInterface::class);

        // Container should first check if service exists
        $mockContainer->expects($this->once())
            ->method('has')
            ->with('TestService')
            ->willReturn(true);

        // Then retrieve the service
        $mockContainer->expects($this->once())
            ->method('get')
            ->with('TestService')
            ->willReturn($mockService);

        // Inject the mock container into the test class
        $testClass = new TestClassWithServiceContainer();
        $reflection = new ReflectionClass($testClass);
        $containerProperty = $reflection->getProperty('container');
        $containerProperty->setValue($testClass, $mockContainer);

        $result = $testClass->getServiceFromContainer('TestService');

        // Verify correct service is returned
        $this->assertSame($mockService, $result);
    }

    /**
     * Tests behavior when requested service is not available
     *
     * Verifies that:
     * - Container properly checks service availability
     * - No attempt is made to retrieve non-existent service
     * - Null is returned for unavailable services
     */
    public function testGetServiceFromContainerWhenServiceNotAvailable(): void
    {
        $mockContainer = $this->createMock(ContainerInterface::class);

        // Container should indicate service is not available
        $mockContainer->expects($this->once())
            ->method('has')
            ->with('NonExistentService')
            ->willReturn(false);

        // Get should never be called for non-existent service
        $mockContainer->expects($this->never())
            ->method('get');

        // Setup test class with mock container
        $testClass = new TestClassWithServiceContainer();
        $reflection = new ReflectionClass($testClass);
        $containerProperty = $reflection->getProperty('container');
        $containerProperty->setValue($testClass, $mockContainer);

        $result = $testClass->getServiceFromContainer('NonExistentService');

        // Verify null is returned for non-existent service
        $this->assertNull($result);
    }

    /**
     * Tests error handling when service retrieval throws an exception
     *
     * Verifies that:
     * - Exceptions during service retrieval are caught
     * - The trait returns null instead of throwing the exception
     * - The application can continue functioning
     */
    public function testGetServiceFromContainerWithException(): void
    {
        // Configure container to throw exception during service retrieval
        $mockContainer = $this->createMock(ContainerInterface::class);
        $mockContainer->expects($this->once())
            ->method('has')
            ->with('TestService')
            ->willReturn(true);
        $mockContainer->expects($this->once())
            ->method('get')
            ->with('TestService')
            ->willThrowException($this->createMock(NotFoundExceptionInterface::class));

        // Inject mock container
        $testClass = new TestClassWithServiceContainer();
        $reflection = new ReflectionClass($testClass);
        $containerProperty = $reflection->getProperty('container');
        $containerProperty->setValue($testClass, $mockContainer);

        $result = $testClass->getServiceFromContainer('TestService');

        // Verify null is returned when exception occurs
        $this->assertNull($result);
    }

    /**
     * Tests the container factory retrieval
     *
     * Verifies that:
     * - The factory instance is correctly retrieved
     * - It's the singleton instance from ContainerFactory
     * - The factory is of the correct type
     */
    public function testGetContainerFactory(): void
    {
        // Use reflection to access protected method
        $reflection = new ReflectionClass($this->traitObject);
        $method = $reflection->getMethod('getContainerFactory');

        $result = $method->invoke($this->traitObject);

        // Verify factory instance
        $this->assertInstanceOf(ContainerFactory::class, $result);
        // Verify it's the singleton instance
        $this->assertSame(ContainerFactory::getInstance(), $result);
    }

    /**
     * Tests the container setter method
     *
     * Verifies that:
     * - Container can be manually set
     * - The set container is properly stored
     * - The container can be retrieved
     */
    public function testSetContainer(): void
    {
        $mockContainer = $this->createMock(ContainerInterface::class);

        $testClass = new TestClassWithServiceContainer();
        $testClass->setContainer($mockContainer);

        // Verify container was properly set
        $reflection = new ReflectionClass($testClass);
        $containerProperty = $reflection->getProperty('container');

        $this->assertSame($mockContainer, $containerProperty->getValue($testClass));
    }

    /**
     * Tests behavior when no container is available
     *
     * Verifies that:
     * - Null is returned when no container is set
     * - No errors occur when accessing services without container
     */
    public function testGetServiceFromContainerWithNoContainer(): void
    {
        $testClass = new TestClassWithServiceContainer();

        $result = $testClass->getServiceFromContainer('TestService');

        // Verify null is returned when no container exists
        $this->assertNull($result);
    }
}
