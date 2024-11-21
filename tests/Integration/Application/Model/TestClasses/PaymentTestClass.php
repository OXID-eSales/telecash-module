<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\TeleCash\Tests\Integration\Application\Model\TestClasses;

use OxidSolutionCatalysts\TeleCash\Exception\TeleCashException;
use OxidSolutionCatalysts\TeleCash\Extension\Application\Model\Payment;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleSettingsServiceInterface;
use Psr\Container\ContainerInterface;

/**
 * Test Class for Payment Model Extension
 *
 * This class extends the Payment model to facilitate testing by:
 * - Providing controlled validation results
 * - Adding debugging capabilities
 * - Exposing protected methods for testing
 * - Simulating parent class behavior
 *
 * Testing strategy:
 * - Bypasses OXID framework dependencies using initParent parameter
 * - Provides direct control over validation results
 * - Enables detailed debugging of validation flow
 * - Maintains test isolation while preserving functionality
 */
class PaymentTestClass extends Payment
{
    /**
     * Controls the parent validation result for testing
     * @var bool
     */
    private bool $parentValidationResult = true;

    /**
     * Enables/disables debug mode for detailed validation tracking
     * @var bool
     */
    private bool $debugMode = false;

    /**
     * Stores debug information during validation process
     * @var array
     */
    private array $debugInfo = [];

    /**
     * Controls the module settings validation result for testing
     * @var bool
     */
    private bool $forceModuleSettingsValid = true;

    /**
     * Creates a new instance of the payment test class
     *
     * @param bool $initParent Whether to initialize the parent OXID model
     * @param ModuleSettingsServiceInterface $moduleSettings Module settings service
     * @param ContainerInterface|null $container Optional container for other services
     */
    public function __construct(
        bool $initParent,
        ModuleSettingsServiceInterface $moduleSettings,
        ?ContainerInterface $container = null
    ) {
        // Überschreiben der moduleSettings Property vor dem Parent-Konstruktor
        $this->moduleSettings = $moduleSettings;

        if ($container) {
            $this->setContainer($container);
        }

        if ($initParent) {
            parent::__construct(true);
        }
    }

    /**
     * Makes the protected setContainer method accessible for testing
     *
     * @param ContainerInterface $container The service container to use
     */
    public function publicSetContainer(ContainerInterface $container): void
    {
        $this->setContainer($container);
    }

    /**
     * Override to prevent service container lookup for moduleSettings
     */
    protected function initServices(): void
    {
        // Do nothing - services are injected in constructor
    }

    /**
     * Overrides the parent validation method for testing
     *
     * Implements the validation logic while adding debugging capabilities
     * and using test-specific validation results.
     *
     * @param array $aDynValue Dynamic values for validation
     * @param string $sShopId Shop ID
     * @param User $oUser User object
     * @param float $dBasketPrice Basket price
     * @param string $sShipSetId Shipping set ID
     * @return bool Whether the payment is valid
     * @throws TeleCashException
     */
    public function isValidPayment($aDynValue, $sShopId, $oUser, $dBasketPrice, $sShipSetId)
    {
        if ($this->debugMode) {
            $this->debugInfo['parentValidation'] = $this->parentValidationResult;
        }

        $result = $this->parentValidationResult;
        if ($result) {
            $result = $this->isTeleCashPaymentValid();
        }

        if ($this->debugMode) {
            $this->debugInfo['finalResult'] = $result;
        }

        return $result;
    }

    /**
     * Overrides the module settings validation for testing purposes
     *
     * Uses a controlled validation result instead of actual module settings
     * to facilitate isolated testing.
     *
     * @internal This method is intended for testing purposes only
     * @return bool The forced validation result
     */
    protected function isValidTeleCashConfiguration(): bool
    {
        $result = $this->forceModuleSettingsValid;
        if ($this->debugMode) {
            $this->debugInfo['isValidConfig'] = $result;
        }
        return $result;
    }

    /**
     * Sets the module settings validation result for testing
     *
     * Allows tests to control whether module settings should be considered valid
     *
     * @param bool $isValid The desired validation result
     * @internal This method is intended for testing purposes only
     */
    public function setModuleSettingsValidationResult(bool $isValid): void
    {
        $this->forceModuleSettingsValid = $isValid;
    }

    /**
     * Sets the parent validation result for testing
     *
     * @param bool $result The desired parent validation result
     */
    public function setParentValidationResult(bool $result): void
    {
        $this->parentValidationResult = $result;
    }

    /**
     * Gets the current parent validation result
     *
     * @return bool The current parent validation result
     */
    public function getParentValidationResult(): bool
    {
        return $this->parentValidationResult;
    }

    /**
     * Provides a static payment ID for testing
     *
     * @return string The test payment ID
     */
    public function getId(): string
    {
        return 'testPaymentId';
    }

    /**
     * Enables/disables debug mode
     *
     * When enabled, collects detailed information about the validation process
     *
     * @param bool $mode Whether to enable debug mode
     */
    public function setDebugMode(bool $mode): void
    {
        $this->debugMode = $mode;
        $this->debugInfo = [];
    }

    /**
     * Returns collected debug information
     *
     * @return array Debug information collected during validation
     */
    public function getDebugInfo(): array
    {
        return $this->debugInfo;
    }

    /**
     * Overrides TeleCash payment check for debugging
     *
     * Adds debug information while maintaining parent functionality
     *
     * @return bool Whether the payment is a TeleCash payment
     */
    public function isTeleCashPayment(): bool
    {
        $result = parent::isTeleCashPayment();
        if ($this->debugMode) {
            $this->debugInfo['isTeleCashPayment'] = $result;
        }
        return $result;
    }
}
