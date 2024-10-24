<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\Application\Controller\Admin;

use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\Utils;
use OxidSolutionCatalysts\TeleCash\Application\Controller\Admin\AdminTeleCashJsonEndpoint;
use OxidSolutionCatalysts\TeleCash\Application\Model\TeleCashPayment;
use OxidSolutionCatalysts\TeleCash\Core\Module;
use OxidSolutionCatalysts\TeleCash\Core\Service\OxNewService;
use OxidSolutionCatalysts\TeleCash\Core\Service\RegistryService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * Class AdminTeleCashJsonEndpointTest
 *
 * This test class verifies the functionality of the AdminTeleCashJsonEndpoint controller.
 * The controller is responsible for providing TeleCash-specific JSON endpoints
 * in the admin area.
 *
 * Main functionalities tested:
 * - Retrieval of possible TeleCash capture types
 * - Error handling for missing services
 * - Validation of JSON output
 *
 * @package OxidSolutionCatalysts\TeleCash\Tests\Unit\Application\Controller\Admin
 */
class AdminTeleCashJsonEndpointTest extends TestCase
{
    private AdminTeleCashJsonEndpoint $controller;
    private RegistryService|MockObject $registryService;
    private OxNewService|MockObject $oxNewService;
    private Request|MockObject $request;
    private Utils|MockObject $utils;
    private TeleCashPayment|MockObject $teleCashPayment;
    private ContainerInterface|MockObject $container;

    /**
     * Sets up the test environment.
     *
     * Creates all necessary mock objects and initializes the controller
     * with required dependencies:
     * - RegistryService for general shop functionalities
     * - Request for HTTP request handling
     * - Utils for response handling
     * - OxNewService for object instantiation
     * - Container for dependency injection
     * - TeleCashPayment for payment-specific functionalities
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Mock Registry Service
        $this->registryService = $this->createMock(RegistryService::class);

        // Mock Request
        $this->request = $this->createMock(Request::class);
        $this->registryService->method('getRequest')
            ->willReturn($this->request);

        // Mock Utils
        $this->utils = $this->createMock(Utils::class);
        $this->registryService->method('getUtils')
            ->willReturn($this->utils);

        // Mock OxNewService
        $this->oxNewService = $this->createMock(OxNewService::class);

        // Mock TeleCashPayment
        $this->teleCashPayment = $this->createMock(TeleCashPayment::class);

        // Configure service mocks
        $this->oxNewService->method('oxNew')
            ->with(TeleCashPayment::class)
            ->willReturn($this->teleCashPayment);

        // Mock PSR Container
        $this->container = $this->createMock(ContainerInterface::class);

        // Configure container for both OxNewService and RegistryService
        $this->container->method('has')
            ->willReturnMap([
                [OxNewService::class, true],
                [RegistryService::class, true]
            ]);

        $this->container->method('get')
            ->willReturnMap([
                [OxNewService::class, $this->oxNewService],
                [RegistryService::class, $this->registryService]
            ]);

        // Create controller instance
        $this->controller = new AdminTeleCashJsonEndpoint($this->registryService, false);

        // Inject container
        $reflection = new \ReflectionClass($this->controller);
        $containerProperty = $reflection->getProperty('container');
        $containerProperty->setValue($this->controller, $this->container);
    }

    /**
     * Tests successful retrieval of TeleCash capture types.
     *
     * Scenario:
     * - A valid TeleCash identifier is provided
     * - The payment model returns valid capture types
     * - The response contains the expected types in JSON format
     *
     * Expected outcome:
     * - A JSON response containing the available capture types is sent
     * - The content-type header is set correctly
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
     * Tests behavior when payment model is unavailable.
     *
     * Scenario:
     * - The service container cannot provide the payment model
     * - The controller must handle this error case gracefully
     *
     * Expected outcome:
     * - An empty JSON array is sent as response
     * - The content-type header is set correctly
     */
    public function testGetPossibleTeleCashCaptureTypesWithNoPaymentModel(): void
    {
        // Create new container that doesn't provide OxNewService
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')
            ->willReturnMap([
                [OxNewService::class, false],
                [RegistryService::class, true]
            ]);

        $container->method('get')
            ->willReturnMap([
                [RegistryService::class, $this->registryService]
            ]);

        // Inject new container
        $reflection = new \ReflectionClass($this->controller);
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
     * Tests behavior with invalid input data.
     *
     * Scenario:
     * - An invalid or missing TeleCash identifier is provided
     * - The controller must detect this and respond accordingly
     *
     * Expected outcome:
     * - An empty JSON array is sent as response
     * - The content-type header is set correctly
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
