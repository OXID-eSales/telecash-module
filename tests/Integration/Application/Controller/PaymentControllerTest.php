<?php

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Tests\Integration\Application\Controller;

use OxidSolutionCatalysts\TeleCash\Core\Service\OxNewService;
use OxidSolutionCatalysts\TeleCash\Exception\TeleCashException;
use OxidSolutionCatalysts\TeleCash\Extension\Application\Controller\PaymentController;
use OxidSolutionCatalysts\TeleCash\Core\Service\TranslateServiceInterface;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashConnect;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleSettingsServiceInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * Integration Test for the TeleCash PaymentController extension
 *
 * This test class demonstrates how to test an OXID controller extension that uses
 * dependency injection and the service container pattern. It serves as a template
 * for testing OXID controller extensions.
 */
class PaymentControllerTest extends TestCase
{
    private PaymentController $controller;
    private $telecashConnectMock;
    private $translateServiceMock;
    private $containerMock;
    private $oxNewServiceMock;
    private $moduleSettingsServiceMock;

    /**
     * Sets up the test environment
     *
     * This method demonstrates a complex setup for testing OXID controller extensions:
     * 1. Creates all necessary service mocks
     * 2. Configures a mock container with these services
     * 3. Creates a testable controller instance using anonymous class
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create mock for payment processing service
        $this->telecashConnectMock = $this->createMock(TeleCashConnect::class);

        // Create mock for translation handling
        $this->translateServiceMock = $this->createMock(TranslateServiceInterface::class);

        // Create and configure mock for module settings
        // This provides configuration values like store ID and secrets
        $this->moduleSettingsServiceMock = $this->createMock(ModuleSettingsServiceInterface::class);
        $this->moduleSettingsServiceMock->method('getStoreId')->willReturn('store123');
        $this->moduleSettingsServiceMock->method('getSharedSecret')->willReturn('secret123');

        // Create mock for OXID's object creation service
        // Configure it to return our TeleCashConnect mock when requested
        $this->oxNewServiceMock = $this->createMock(OxNewService::class);
        $this->oxNewServiceMock->method('oxNew')
            ->willReturnCallback(function ($className, $args = []) {
                if ($className === TeleCashConnect::class) {
                    return $this->telecashConnectMock;
                }
                return null;
            });

        // Create and configure the DI container mock
        $this->containerMock = $this->createMock(ContainerInterface::class);

        // Configure container to acknowledge our services
        $this->containerMock->method('has')
            ->willReturnCallback(function ($serviceId) {
                return in_array($serviceId, [
                    TranslateServiceInterface::class,
                    ModuleSettingsServiceInterface::class,
                    OxNewService::class,
                    'telecash_connect'
                ]);
            });

        // Configure container to return our service mocks
        $this->containerMock->method('get')
            ->willReturnCallback(function ($serviceId) {
                return match ($serviceId) {
                    TranslateServiceInterface::class => $this->translateServiceMock,
                    ModuleSettingsServiceInterface::class => $this->moduleSettingsServiceMock,
                    OxNewService::class => $this->oxNewServiceMock,
                    default => throw new \RuntimeException("Service not found: $serviceId")
                };
            });

        // Create testable controller instance using anonymous class
        // This approach allows us to override the container while maintaining original functionality
        $this->controller = new class ($this->containerMock) extends PaymentController {
            private ContainerInterface $testContainer;

            public function __construct(ContainerInterface $container)
            {
                $this->testContainer = $container;
                parent::__construct();
            }

            protected function getContainer(): ContainerInterface
            {
                return $this->testContainer;
            }
        };
    }

    /**
     * Tests error handling when TeleCash returns a valid error response
     *
     * Verifies that:
     * - POST data is properly processed
     * - Response validation works
     * - Error message is correctly extracted and stored
     *
     * @throws TeleCashException
     */
    public function testProvideTeleCashErrorWithValidFailReason(): void
    {
        // Arrange
        $_POST = ['some_data' => 'value'];
        $expectedFailReason = 'Payment declined';

        // Configure mock to expect POST data processing
        $this->telecashConnectMock->expects($this->once())
            ->method('setResponseData')
            ->with($_POST);

        // Configure mock to return valid response status
        $this->telecashConnectMock->expects($this->once())
            ->method('isValidResponse')
            ->willReturn(true);

        // Configure mock to return error message
        $this->telecashConnectMock->expects($this->once())
            ->method('getTransactionResult')
            ->willReturn(['fail_reason' => $expectedFailReason]);

        // Act
        $this->controller->provideTeleCashError();

        // Assert
        $this->assertEquals($expectedFailReason, $this->controller->getPaymentErrorText());
    }

    /**
     * Tests error handling when TeleCash returns an invalid response
     *
     * Verifies that:
     * - Invalid responses are properly detected
     * - Default error message is retrieved from translation service
     * - Default error message is stored
     *
     * @throws TeleCashException
     */
    public function testProvideTeleCashErrorWithInvalidResponse(): void
    {
        // Arrange
        $_POST = ['some_data' => 'value'];
        $defaultError = 'Default error message';

        $this->telecashConnectMock->expects($this->once())
            ->method('setResponseData')
            ->with($_POST);

        $this->telecashConnectMock->expects($this->once())
            ->method('isValidResponse')
            ->willReturn(false);

        $this->translateServiceMock->expects($this->once())
            ->method('translateString')
            ->with('TELECASH_DEFAULT_PAYMENT_ERROR')
            ->willReturn($defaultError);

        // Act
        $this->controller->provideTeleCashError();

        // Assert
        $this->assertEquals($defaultError, $this->controller->getPaymentErrorText());
    }

    /**
     * Tests error handling when TeleCash returns an empty error reason
     *
     * Verifies that:
     * - Empty error messages are properly handled
     * - Default error message is used as fallback
     * - Translation service is properly utilized
     *
     * @throws TeleCashException
     */
    public function testProvideTeleCashErrorWithEmptyFailReason(): void
    {
        // Arrange
        $_POST = ['some_data' => 'value'];
        $defaultError = 'Default error message';

        $this->telecashConnectMock->expects($this->once())
            ->method('setResponseData')
            ->with($_POST);

        $this->telecashConnectMock->expects($this->once())
            ->method('isValidResponse')
            ->willReturn(true);

        $this->telecashConnectMock->expects($this->once())
            ->method('getTransactionResult')
            ->willReturn(['fail_reason' => '']);

        $this->translateServiceMock->expects($this->once())
            ->method('translateString')
            ->with('TELECASH_DEFAULT_PAYMENT_ERROR')
            ->willReturn($defaultError);

        // Act
        $this->controller->provideTeleCashError();

        // Assert
        $this->assertEquals($defaultError, $this->controller->getPaymentErrorText());
    }
}
