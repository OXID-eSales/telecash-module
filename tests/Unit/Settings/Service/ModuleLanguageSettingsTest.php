<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\Settings\Service;

use OxidEsales\Eshop\Core\Exception\LanguageNotFoundException;
use OxidEsales\Eshop\Core\Language;
use OxidSolutionCatalysts\TeleCash\Core\Service\RegistryService;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleLanguageSettingsService;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleLanguageSettingsServiceInterface;
use OxidSolutionCatalysts\TeleCash\Core\Module;

/**
 * Test class for ModuleLanguageSettingsService
 *
 * This test suite validates the functionality of the ModuleLanguageSettingsService,
 * which is responsible for managing language-specific settings in the OXID module.
 */
class ModuleLanguageSettingsTest extends TestCase
{
    /**
     * The service instance we want to test
     */
    private ModuleLanguageSettingsService $service;

    /**
     * Mock object for the module setting service
     * We use this to avoid actual database operations during testing
     */
    private MockObject $moduleSettingService;

    /**
     * Mock for the OXID registry service
     */
    private MockObject $registryService;

    /**
     * Mock for the OXID language object
     */
    private MockObject $oxidLang;

    /**
     * Set up method runs before each test
     * Creates fresh instances of the service and its dependencies
     */
    protected function setUp(): void
    {
        // Create mock objects for all dependencies
        $this->moduleSettingService = $this->createMock(ModuleSettingServiceInterface::class);
        $this->registryService = $this->createMock(RegistryService::class);
        $this->oxidLang = $this->createMock(Language::class);

        // Setup registry service to return the language mock by default
        $this->registryService
            ->method('getLang')
            ->willReturn($this->oxidLang);

        // Initialize the service with the mocks
        $this->service = new ModuleLanguageSettingsService(
            $this->moduleSettingService,
            $this->registryService
        );
    }

    /**
     * Test if getLanguages() correctly retrieves the language collection from the module settings
     *
     * This test verifies that:
     * 1. The correct method is called on the module setting service
     * 2. The correct parameters are passed
     * 3. The returned data is exactly what we expect
     */
    public function testGetLanguagesReturnsArrayFromModuleSettings(): void
    {
        // Define the expected languages that should be returned
        $expectedLanguages = [
            'de' => 'de_DE',
            'en' => 'en_US',
            'fr' => 'fr_FR'
        ];

        // Configure the mock to expect the getCollection call and return our test data
        $this->moduleSettingService
            ->expects($this->once())
            ->method('getCollection')
            ->with(
                ModuleLanguageSettingsServiceInterface::LANGUAGES,
                Module::MODULE_ID
            )
            ->willReturn($expectedLanguages);

        // Execute the method we want to test
        $result = $this->service->getLanguages();

        // Verify the result matches our expectations
        $this->assertEquals($expectedLanguages, $result);
    }

    /**
     * Test if saveLanguages() correctly stores the language collection in the module settings
     *
     * This test ensures that:
     * 1. The correct method is called on the module setting service
     * 2. The correct parameters are passed, including the data to be saved
     */
    public function testSaveLanguagesSavesArrayToModuleSettings(): void
    {
        // Define test language data to be saved
        $languages = [
            'de' => 'de_DE',
            'en' => 'en_US',
            'fr' => 'fr_FR'
        ];

        // Configure the mock to expect the saveCollection call with our test data
        $this->moduleSettingService
            ->expects($this->once())
            ->method('saveCollection')
            ->with(
                ModuleLanguageSettingsServiceInterface::LANGUAGES,
                $languages,
                Module::MODULE_ID
            );

        // Execute the method we want to test
        $this->service->saveLanguages($languages);
    }

    /**
     * Test cases for getLocaleForCountryIso with explicitly provided ISO codes
     *
     * @dataProvider localeForExplicitCountryIsoProvider
     */
    public function testGetLocaleForExplicitCountryIso(string $countryIso, string $expectedLocale): void
    {
        // Configure the mock to return our test language settings
        $this->moduleSettingService
            ->method('getCollection')
            ->willReturn([
                'de' => 'de_DE',
                'en' => 'en_US'
            ]);

        // Execute the method we want to test
        $result = $this->service->getLocaleForCountryIso($countryIso);

        // Verify the result matches our expectations
        $this->assertEquals($expectedLocale, $result);
    }

    /**
     * Test getLocaleForCountryIso when no ISO is provided (empty string)
     * Should fall back to OXID shop language
     */
    public function testGetLocaleForCountryIsoWithEmptyStringUsesOxidLanguage(): void
    {
        // Configure the mock to return our test language settings
        $this->moduleSettingService
            ->method('getCollection')
            ->willReturn([
                'de' => 'de_DE',
                'en' => 'en_US'
            ]);

        // Configure oxidLang mock to return a specific language
        $this->oxidLang
            ->method('getLanguageAbbr')
            ->willReturn('de');

        // Execute the method with empty string
        $result = $this->service->getLocaleForCountryIso('');

        // Should return the locale matching the OXID language
        $this->assertEquals('de_DE', $result);
    }

    /**
     * Test handling of LanguageNotFoundException
     * Should return default locale when OXID language throws exception
     */
    public function testGetLocaleForCountryIsoHandlesLanguageNotFoundException(): void
    {
        // Configure the mock to return our test language settings
        $this->moduleSettingService
            ->method('getCollection')
            ->willReturn([
                'de' => 'de_DE',
                'en' => 'en_US'
            ]);

        // Configure oxidLang mock to throw exception
        $this->oxidLang
            ->method('getLanguageAbbr')
            ->willThrowException(new LanguageNotFoundException());

        // Execute the method with empty string
        $result = $this->service->getLocaleForCountryIso('');

        // Should return default locale when language not found
        $this->assertEquals(ModuleLanguageSettingsServiceInterface::DEFAULT_LOCALE, $result);
    }

    /**
     * Data provider for testGetLocaleForExplicitCountryIso
     *
     * Provides different test scenarios:
     * 1. Normal case with lowercase ISO code
     * 2. Case insensitive test with uppercase ISO code
     * 3. Handling of non-existing ISO code
     *
     * @return array Test cases with [countryIso, expectedLocale]
     */
    public function localeForExplicitCountryIsoProvider(): array
    {
        return [
            'existing lowercase iso' => [
                'de',  // Input ISO code
                'de_DE'  // Expected result
            ],
            'existing uppercase iso' => [
                'DE',  // Tests case insensitivity
                'de_DE'
            ],
            'non-existing iso returns default' => [
                'fr',  // ISO code that doesn't exist in settings
                ModuleLanguageSettingsServiceInterface::DEFAULT_LOCALE
            ]
        ];
    }
}
