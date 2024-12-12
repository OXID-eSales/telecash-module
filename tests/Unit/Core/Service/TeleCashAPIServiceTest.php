<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\Core\Service;

use OxidSolutionCatalysts\TeleCash\Core\Service\Logger;
use OxidSolutionCatalysts\TeleCash\Core\Service\TeleCashAPIService;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCash;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleFileSettingsServiceInterface;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleSettingsServiceInterface;
use PHPUnit\Framework\TestCase;

/**
 * Test cases for the TeleCashAPIService class
 *
 * This test suite verifies that the TeleCashAPIService correctly configures
 * and instantiates the TeleCash API client using values from the module settings
 * and file settings services.
 */
class TeleCashAPIServiceTest extends TestCase
{
    /**
     * Mock object for module settings service
     * Provides configuration values like user credentials and API URLs
     */
    private $moduleSettingsMock;

    /**
     * Mock object for module file settings service
     * Provides paths to certificate files and other filesystem resources
     */
    private $moduleFileSettingsMock;

    /**
     * Mock object for the logger
     * Used for debugging and tracking API interactions
     */
    private $loggerMock;

    /**
     * Instance of the service under test
     */
    private $service;

    /**
     * Set up the test environment before each test method
     *
     * Creates mock objects for all dependencies and initializes
     * the TeleCashAPIService with these mocks. This ensures
     * each test starts with a clean state.
     */
    protected function setUp(): void
    {
        // Create mocks for all dependencies
        $this->moduleSettingsMock = $this->createMock(ModuleSettingsServiceInterface::class);
        $this->moduleFileSettingsMock = $this->createMock(ModuleFileSettingsServiceInterface::class);
        $this->loggerMock = $this->createMock(Logger::class);

        // Initialize the service with mocked dependencies
        // This allows us to control and verify all external interactions
        $this->service = new TeleCashAPIService(
            $this->moduleSettingsMock,
            $this->moduleFileSettingsMock,
            $this->loggerMock
        );
    }

    /**
     * Test that getTeleCashAPI returns a properly configured TeleCash instance
     *
     * This test verifies that:
     * 1. All necessary configuration methods are called exactly once
     * 2. The service retrieves all required configuration values
     * 3. A valid TeleCash instance is returned
     *
     * The test uses mock expectations to ensure the service interacts
     * correctly with its dependencies and properly configures the
     * TeleCash client with the retrieved values.
     */
    public function testGetTeleCashAPIReturnsConfiguredInstance(): void
    {
        // Configure the file settings mock to return test certificate paths
        // These paths would point to SSL/TLS certificates in a real environment
        $this->moduleFileSettingsMock
            ->expects($this->once())
            ->method('getClientCertificateP12FilePath')
            ->willReturn('/path/to/client.p12');

        $this->moduleFileSettingsMock
            ->expects($this->once())
            ->method('getClientCertificatePrivateKeyFilePath')
            ->willReturn('/path/to/private.key');

        $this->moduleFileSettingsMock
            ->expects($this->once())
            ->method('getTrustAnchorPEMFilePath')
            ->willReturn('/path/to/ca.pem');

        // Configure the module settings mock to return test credentials
        // In a real environment, these would be the merchant's API credentials
        $this->moduleSettingsMock
            ->expects($this->once())
            ->method('getUserId')
            ->willReturn('test-user');

        $this->moduleSettingsMock
            ->expects($this->once())
            ->method('getBasicAuthPassword')
            ->willReturn('test-password');

        $this->moduleSettingsMock
            ->expects($this->once())
            ->method('getClientCertificateInstallationPassword')
            ->willReturn('cert-password');

        // Configure the service URL - this would be different for sandbox/production
        $this->moduleSettingsMock
            ->expects($this->once())
            ->method('getServiceUrl')
            ->willReturn('https://test.ipg-online.com/ipgapi/services');

        // Call the method under test
        $result = $this->service->getTeleCashAPI();

        // Verify that the method returns a properly instantiated TeleCash object
        // Note: We can only verify the type here as the TeleCash class internals
        // are not accessible for detailed configuration verification
        $this->assertInstanceOf(TeleCash::class, $result);
    }
}
