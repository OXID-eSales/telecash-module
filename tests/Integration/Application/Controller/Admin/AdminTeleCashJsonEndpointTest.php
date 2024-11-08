<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Integration\Application\Controller\Admin;

use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\Utils;
use OxidSolutionCatalysts\TeleCash\Application\Controller\Admin\AdminTeleCashJsonEndpoint;
use OxidSolutionCatalysts\TeleCash\Application\Model\TeleCashPayment;
use OxidSolutionCatalysts\TeleCash\Core\Module;
use OxidSolutionCatalysts\TeleCash\Core\Service\OxNewService;
use OxidSolutionCatalysts\TeleCash\Core\Service\RegistryService;
use OxidSolutionCatalysts\TeleCash\Core\Service\TeleCashPaymentValidatorServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use RuntimeException;

/**
 * Class AdminTeleCashJsonEndpointTest
 *
 * Integration test class for the AdminTeleCashJsonEndpoint controller.
 * These tests verify the interaction between the controller and its dependencies
 * in the OXID admin area.
 *
 * Main functionalities tested:
 * - Retrieval of TeleCash capture types
 * - Error handling for missing or invalid services
 * - Proper JSON response formatting
 * - Integration with OXID registry and container services
 *
 * Test coverage includes:
 * - Success scenarios with valid data
 * - Error handling with missing payment models
 * - Error handling with invalid input data
 * - Service container interaction
 * - Registry service integration
 *
 * @package OxidSolutionCatalysts\TeleCash\Tests\Integration\Application\Controller\Admin
 */
class AdminTeleCashJsonEndpointTest extends TestCase
{
    /**
     * @var AdminTeleCashJsonEndpoint The controller instance under test
     */
    private AdminTeleCashJsonEndpoint $controller;

    /**
     * @var MockObject&RegistryService Mock for OXID registry service
     * Handles core shop functionality access
     */
    private MockObject $registryService;

    /**
     * @var MockObject&OxNewService Mock for OXID object creation service
     * Manages instantiation of shop objects
     */
    private MockObject $oxNewService;

    /**
     * @var MockObject&Request Mock for HTTP request handling
     * Manages incoming request parameters
     */
    private MockObject $request;

    /**
     * @var MockObject&Utils Mock for OXID utility functions
     * Handles response output and headers
     */
    private MockObject $utils;

    /**
     * @var MockObject&TeleCashPayment Mock for TeleCash payment model
     * Manages TeleCash-specific payment operations
     */
    private MockObject $teleCashPayment;

    /**
     * @var MockObject&ContainerInterface Mock for PSR-11 container
     * Handles dependency injection
     */
    private MockObject $container;

    /**
     * @var MockObject&TeleCashPaymentValidatorServiceInterface Mock for payment validator
     * Validates TeleCash payment operations
     */
    private MockObject $validatorService;

    /**
     * Sets up the test environment before each test.
     *
     * This method:
     * 1. Creates mock objects for all required dependencies
     * 2. Configures the mock behaviors
     * 3. Sets up the service container
     * 4. Initializes the controller with mocked dependencies
     *
     * Mock configuration includes:
     * - Registry service with request and utils functionality
     * - Container service with dependency resolution
     * - OxNew service for object creation
     * - Payment validator service
     *
     * All dependencies are configured to work together to test
     * the controller's functionality in isolation while maintaining
     * realistic behavior.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create all mocks first
        $this->registryService = $this->createMock(RegistryService::class);
        $this->request = $this->createMock(Request::class);
        $this->utils = $this->createMock(Utils::class);
        $this->oxNewService = $this->createMock(OxNewService::class);
        $this->teleCashPayment = $this->createMock(TeleCashPayment::class);
        $this->validatorService = $this->createMock(TeleCashPaymentValidatorServiceInterface::class);
        $this->container = $this->createMock(ContainerInterface::class);

        // Configure Registry Service dependencies
        $this->registryService->method('getRequest')
            ->willReturn($this->request);
        $this->registryService->method('getUtils')
            ->willReturn($this->utils);

        // Configure Container
        $this->container->method('has')
            ->willReturnCallback(function ($service) {
                return match ($service) {
                    OxNewService::class,
                    RegistryService::class,
                    TeleCashPaymentValidatorServiceInterface::class => true,
                    default => false
                };
            });

        $this->container->method('get')
            ->willReturnCallback(function ($service) {
                return match ($service) {
                    OxNewService::class => $this->oxNewService,
                    RegistryService::class => $this->registryService,
                    TeleCashPaymentValidatorServiceInterface::class => $this->validatorService,
                    default => throw new RuntimeException("Unexpected service requested: " . $service)
                };
            });

        // Configure oxNew service
        $this->oxNewService->method('oxNew')
            ->with(TeleCashPayment::class, [$this->validatorService])
            ->willReturn($this->teleCashPayment);

        // Create and configure controller
        $this->controller = new AdminTeleCashJsonEndpoint(false);

        // Set up controller using reflection
        $reflection = new ReflectionClass($this->controller);

        $registryProperty = $reflection->getProperty('registryService');
        $registryProperty->setValue($this->controller, $this->registryService);

        $containerProperty = $reflection->getProperty('container');
        $containerProperty->setValue($this->controller, $this->container);
    }

    /**
     * Tests successful retrieval of TeleCash capture types.
     *
     * This test verifies that the controller correctly:
     * 1. Accepts a valid TeleCash identifier
     * 2. Retrieves possible capture types from the payment model
     * 3. Returns the data in proper JSON format
     *
     * Test scenario:
     * - Given: A valid TeleCash identifier
     * - When: The capture types are requested
     * - Then: A JSON response with the correct types is returned
     *
     * Verifies:
     * - Correct parameter handling
     * - Proper model interaction
     * - Valid JSON response format
     * - Correct content-type header
     */
    public function testGetPossibleTeleCashCaptureTypesWithValidData(): void
    {
        $testIdent = 'test-ident';
        $expectedTypes = ['TYPE1', 'TYPE2'];

        $this->request->method('getRequestEscapedParameter')
            ->with(Module::TELECASH_DB_FIELD_IDENT)
            ->willReturn($testIdent);

        $this->teleCashPayment->method('getPossibleTeleCashCaptureTypes')
            ->with($testIdent)
            ->willReturn($expectedTypes);

        $this->utils->expects($this->once())
            ->method('setHeader')
            ->with('Content-Type: application/json');

        $this->utils->expects($this->once())
            ->method('showMessageAndExit')
            ->with($this->callback(function ($json) use ($expectedTypes) {
                $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
                return $data === $expectedTypes;
            }));

        $this->controller->getPossibleTeleCashCaptureTypes();
    }

    /**
     * Tests error handling when payment model is unavailable.
     *
     * This test verifies the controller's behavior when:
     * 1. The payment service cannot be resolved from the container
     * 2. The system needs to handle this gracefully
     *
     * Test scenario:
     * - Given: A container without payment service
     * - When: The capture types are requested
     * - Then: An empty JSON array is returned
     *
     * Verifies:
     * - Proper error handling
     * - Graceful degradation
     * - Valid error response format
     */
    public function testGetPossibleTeleCashCaptureTypesWithNoPaymentModel(): void
    {
        // Create new container that doesn't provide OxNewService
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')
            ->willReturnMap([
                [OxNewService::class, false],
                [RegistryService::class, true],
                [TeleCashPaymentValidatorServiceInterface::class, true]
            ]);

        $container->method('get')
            ->willReturnMap([
                [RegistryService::class, $this->registryService],
                [TeleCashPaymentValidatorServiceInterface::class, $this->validatorService]
            ]);

        // Inject new container
        $reflection = new ReflectionClass($this->controller);
        $containerProperty = $reflection->getProperty('container');
        $containerProperty->setValue($this->controller, $container);

        $this->utils->expects($this->once())
            ->method('setHeader')
            ->with('Content-Type: application/json');

        $this->utils->expects($this->once())
            ->method('showMessageAndExit')
            ->with($this->callback(function ($json) {
                $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
                return $data === [];
            }));

        $this->controller->getPossibleTeleCashCaptureTypes();
    }

    /**
     * Tests handling of invalid input data.
     *
     * This test verifies the controller's behavior when:
     * 1. An invalid or missing TeleCash identifier is provided
     * 2. The system needs to handle the invalid input
     *
     * Test scenario:
     * - Given: A null or invalid identifier
     * - When: The capture types are requested
     * - Then: An empty JSON array is returned
     *
     * Verifies:
     * - Input validation
     * - Error handling
     * - Proper error response format
     */
    public function testGetPossibleTeleCashCaptureTypesWithInvalidData(): void
    {
        $this->request->method('getRequestEscapedParameter')
            ->with(Module::TELECASH_DB_FIELD_IDENT)
            ->willReturn(null);

        $this->utils->expects($this->once())
            ->method('setHeader')
            ->with('Content-Type: application/json');

        $this->utils->expects($this->once())
            ->method('showMessageAndExit')
            ->with($this->callback(function ($json) {
                $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
                return $data === [];
            }));

        $this->controller->getPossibleTeleCashCaptureTypes();
    }
}
