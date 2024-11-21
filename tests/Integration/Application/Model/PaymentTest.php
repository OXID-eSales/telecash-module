<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\TeleCash\Tests\Integration\Extension\Application\Model;

use OxidEsales\Eshop\Application\Model\User;
use OxidSolutionCatalysts\TeleCash\Application\Model\TeleCashPayment;
use OxidSolutionCatalysts\TeleCash\Core\Service\OxNewService;
use OxidSolutionCatalysts\TeleCash\Core\Service\TeleCashPaymentValidatorServiceInterface;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleSettingsServiceInterface;
use OxidSolutionCatalysts\TeleCash\Tests\Integration\Application\Model\TestClasses\PaymentTestClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Container\ContainerInterface;

/**
 * Integration Test for Payment Model Extension
 *
 * This test suite validates the payment validation logic for both TeleCash and regular payments.
 * It uses a specialized test class (PaymentTestClass) to simulate complex behaviors without
 * requiring full OXID framework integration.
 *
 * Key test aspects:
 * - Regular payment validation (non-TeleCash payments)
 * - TeleCash payment validation with valid/invalid configurations
 * - Service availability and error handling
 *
 * Testing strategy:
 * - Uses partial mocking to avoid OXID framework dependencies
 * - Simulates service container for dependency injection
 * - Provides controlled validation results for ModuleSettings
 * - Tests both success and failure scenarios
 */
class PaymentTest extends TestCase
{
    private PaymentTestClass $payment;
    private ContainerInterface&MockObject $container;
    private OxNewService&MockObject $oxNewService;
    private TeleCashPayment&MockObject $teleCashPayment;
    private TeleCashPaymentValidatorServiceInterface&MockObject $validatorService;
    private ModuleSettingsServiceInterface&MockObject $moduleSettings;

    /**
     * Sets up the test environment before each test
     *
     * Creates and configures:
     * - Mock objects for all required services
     * - Container mock with registered services
     * - Payment test class instance
     *
     * The setup ensures isolated testing without OXID framework dependencies
     * while maintaining the ability to test all payment validation scenarios.
     */
    protected function setUp(): void
    {
        // Create mocks
        $this->container = $this->createMock(ContainerInterface::class);
        $this->oxNewService = $this->createMock(OxNewService::class);
        $this->teleCashPayment = $this->createMock(TeleCashPayment::class);
        $this->validatorService = $this->createMock(TeleCashPaymentValidatorServiceInterface::class);
        $this->moduleSettings = $this->createMock(ModuleSettingsServiceInterface::class);

        // Setup container mock with service registration
        $this->container->method('has')
            ->willReturnCallback(fn($serviceId) => in_array($serviceId, [
                OxNewService::class,
                TeleCashPaymentValidatorServiceInterface::class
            ], true));

        $this->container->method('get')
            ->willReturnCallback(function ($serviceId) {
                return match ($serviceId) {
                    OxNewService::class => $this->oxNewService,
                    TeleCashPaymentValidatorServiceInterface::class => $this->validatorService,
                    default => null
                };
            });

        // Configure OxNew service to create TeleCashPayment instances
        $this->oxNewService->method('oxNew')
            ->with(
                TeleCashPayment::class,
                $this->callback(function ($args) {
                    return isset($args[0]) && $args[0] === $this->validatorService;
                })
            )
            ->willReturn($this->teleCashPayment);

        // Initialize payment model with correct parameter order
        $this->payment = new PaymentTestClass(
            false,
            $this->moduleSettings, // ModuleSettingsServiceInterface mock
            $this->container      // Optional container
        );
    }

    /**
     * Tests validation of regular (non-TeleCash) payments
     *
     * Verifies that:
     * - Regular payments are properly identified
     * - TeleCash-specific validation is skipped
     * - Parent validation result is respected
     */
    public function testRegularPaymentValidation(): void
    {
        $this->teleCashPayment->method('loadByPaymentId')
            ->with('testPaymentId')
            ->willReturn(false);

        // Setup ModuleSettings expectations for regular payment
        $this->moduleSettings->expects($this->never())
            ->method('isValidBackendConfiguration');
        $this->moduleSettings->expects($this->never())
            ->method('isValidFrontendConfiguration');

        $result = $this->payment->isValidPayment([], '1', $this->createMock(User::class), 100.0, '1');
        $this->assertTrue($result);
    }

    /**
     * Tests TeleCash payment validation with valid configuration
     *
     * Verifies that:
     * - TeleCash payments are properly identified
     * - Module settings validation is performed
     * - Payment is valid when configuration is valid
     */
    public function testTeleCashPaymentValidationWithValidConfig(): void
    {
        $this->teleCashPayment->method('loadByPaymentId')
            ->with('testPaymentId')
            ->willReturn(true);

        // Setup ModuleSettings expectations for valid config
        $this->moduleSettings->method('isValidFrontendConfiguration')
            ->willReturn(true);

        $this->payment->setModuleSettingsValidationResult(true);

        $result = $this->payment->isValidPayment([], '1', $this->createMock(User::class), 100.0, '1');
        $this->assertTrue($result);
    }

    /**
     * Tests TeleCash payment validation with invalid configuration
     *
     * Verifies that:
     * - TeleCash payments are properly identified
     * - Invalid module settings result in payment invalidation
     * - Validation failure is properly handled
     */
    public function testTeleCashPaymentValidationWithInvalidConfig(): void
    {
        $this->teleCashPayment->method('loadByPaymentId')
            ->with('testPaymentId')
            ->willReturn(true);

        // Setup ModuleSettings expectations for invalid config
        $this->moduleSettings->method('isValidFrontendConfiguration')
            ->willReturn(false);

        $this->payment->setModuleSettingsValidationResult(false);

        $result = $this->payment->isValidPayment([], '1', $this->createMock(User::class), 100.0, '1');
        $this->assertFalse($result);
    }
}
