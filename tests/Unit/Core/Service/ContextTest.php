<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\Core\Service;

use OxidEsales\Eshop\Core\Config;
use OxidEsales\EshopCommunity\Application\Model\Basket;
use OxidSolutionCatalysts\TeleCash\Core\Module;
use OxidSolutionCatalysts\TeleCash\Core\Service\RegistryService;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleSettingsServiceInterface;
use OxidSolutionCatalysts\TeleCash\Tests\Unit\Core\Service\TestClasses\ContextTestClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Unit Tests for Context Service
 *
 * These tests verify the log file path generation functionality of the Context service,
 * specifically focusing on:
 * - Correct path construction with different base directories
 * - Date-based filename generation
 * - Cross-platform path handling
 * - Edge cases and various date formats
 *
 * Testing strategy:
 * - Uses mock objects for dependencies
 * - Employs data providers for comprehensive test coverage
 * - Tests both standard and edge cases
 * - Ensures consistent results across platforms
 *
 * @package OxidSolutionCatalysts\TeleCash\Tests\Unit\Core\Service
 */
class ContextTest extends TestCase
{
    private Config&MockObject $shopConfig;
    private ContextTestClass $context;
    private string $mockLogsDir = '/var/www/shop/source/log';
    private ModuleSettingsServiceInterface&MockObject $moduleSettings;
    private RegistryService&MockObject $registryService;
    private string $mockShopUrl = 'https://example.com/shop/';


    /**
     * Sets up the test environment
     *
     * Initializes:
     * - Shop configuration mock with predefined log directory
     * - Context test class instance with mocked dependencies
     */
    protected function setUp(): void
    {
        $this->shopConfig = $this->createMock(Config::class);
        $this->shopConfig->method('getLogsDir')
            ->willReturn($this->mockLogsDir);
        $this->shopConfig->method('getCurrentShopUrl')
            ->willReturn($this->mockShopUrl);

        $this->moduleSettings = $this->createMock(ModuleSettingsServiceInterface::class);
        $this->registryService = $this->createMock(RegistryService::class);

        $this->context = new ContextTestClass(
            $this->shopConfig,
            $this->registryService,
            $this->moduleSettings
        );
    }

    /**
     * Tests basic log file path generation
     *
     * Verifies that:
     * - Path structure is correct
     * - Directory separators are properly used
     * - Date is correctly formatted in filename
     */
    public function testGetTeleCashLogFilePath(): void
    {
        $testDate = '2024-01-15';
        $this->context->setFixedDate($testDate);

        $expectedPath = implode(DIRECTORY_SEPARATOR, [
            $this->mockLogsDir,
            Module::MODULE_ID,
            Module::MODULE_ID . '_' . $testDate . '.log'
        ]);

        $actualPath = $this->context->getTeleCashLogFilePath();

        $this->assertEquals(
            $expectedPath,
            $actualPath,
            'Generated log file path should match expected structure'
        );
    }

    /**
     * Tests path generation with different base directories
     *
     * Verifies correct path generation for:
     * - Simple absolute paths
     * - Nested directory structures
     * - Relative paths
     *
     * @dataProvider logDirectoryProvider
     * @param string $baseDir Base directory to test
     * @param string $expectedPrefix Expected path prefix
     * @throws Exception
     */
    public function testGetTeleCashLogFilePathWithDifferentDirectories(
        string $baseDir,
        string $expectedPrefix
    ): void {
        $testDate = '2024-01-15';

        $shopConfig = $this->createMock(Config::class);
        $shopConfig->method('getLogsDir')
            ->willReturn($baseDir);

        // Create new moduleSettings and registryService mock for this test
        $moduleSettings = $this->createMock(ModuleSettingsServiceInterface::class);
        $registryService = $this->createMock(RegistryService::class);

        $context = new ContextTestClass(
            $shopConfig,
            $registryService,
            $moduleSettings
        );

        $context->setFixedDate($testDate);

        $actualPath = $context->getTeleCashLogFilePath();

        $expectedPath = $expectedPrefix .
            DIRECTORY_SEPARATOR . Module::MODULE_ID .
            DIRECTORY_SEPARATOR . Module::MODULE_ID . '_' . $testDate . '.log';

        $this->assertEquals(
            $expectedPath,
            $actualPath,
            'Log file path should be correctly generated regardless of base directory'
        );
    }

    /**
     * Provides test cases for different directory configurations
     *
     * Includes:
     * - Absolute paths
     * - Nested paths
     * - Relative paths
     *
     * @return array<string, array<string, string>> Array of test cases
     */
    public static function logDirectoryProvider(): array
    {
        return [
            'simple_path' => [
                'baseDir' => '/var/log',
                'expectedPrefix' => '/var/log'
            ],
            'nested_path' => [
                'baseDir' => '/var/www/shop/logs',
                'expectedPrefix' => '/var/www/shop/logs'
            ],
            'relative_path' => [
                'baseDir' => 'logs',
                'expectedPrefix' => 'logs'
            ]
        ];
    }

    /**
     * Tests log file path generation with different dates
     *
     * Verifies correct handling of:
     * - Different months and days
     * - Year boundaries
     * - Leap year dates
     *
     * @dataProvider dateProvider
     * @param string $testDate The date to test with
     * @param string $expectedDate The expected date in the filename
     */
    public function testGetTeleCashLogFilePathWithDifferentDates(
        string $testDate,
        string $expectedDate
    ): void {
        $this->context->setFixedDate($testDate);

        $actualPath = $this->context->getTeleCashLogFilePath();

        $expectedPath = implode(DIRECTORY_SEPARATOR, [
            $this->mockLogsDir,
            Module::MODULE_ID,
            Module::MODULE_ID . "_{$expectedDate}.log"
        ]);

        $this->assertEquals(
            $expectedPath,
            $actualPath,
            'Log filename should be correctly generated with different dates'
        );
    }

    /**
     * Tests success URL generation with different basket configurations
     *
     * @dataProvider successUrlConfigProvider
     */
    public function testGetSuccessUrl(
        bool $hasAGB,
        bool $hasDownloadAgreement,
        bool $hasIntangibleAgreement,
        array $expectedParams,
        bool $isLiveMode
    ): void {
        // Configure module settings for API mode
        $this->moduleSettings->expects($this->once())
            ->method('isLiveApiMode')
            ->willReturn($isLiveMode);

        // Configure shop config for agreements
        $this->shopConfig
            ->method('getConfigParam')
            ->willReturnCallback(function ($param) use ($hasAGB, $hasDownloadAgreement, $hasIntangibleAgreement) {
                return match ($param) {
                    'blConfirmAGB' => $hasAGB,
                    'blEnableIntangibleProdAgreement' => ($hasDownloadAgreement || $hasIntangibleAgreement),
                    default => null,
                };
            });

        // Configure basket mock
        $basket = $this->createMock(Basket::class);
        $basket->method('hasArticlesWithDownloadableAgreement')
            ->willReturn($hasDownloadAgreement);
        $basket->method('hasArticlesWithIntangibleAgreement')
            ->willReturn($hasIntangibleAgreement);

        $actualUrl = $this->context->getSuccessUrl($basket);

        // Build expected URL
        $baseParams = [
            'cl' => 'order',
            'fnc' => 'execute'
        ];
        $expectedParams = array_merge($baseParams, $expectedParams);
        if (!$isLiveMode) {
            $expectedParams['XDEBUG_SESSION_START'] = '1';
        }
        $expectedUrl = $this->mockShopUrl . 'index.php?' . http_build_query($expectedParams);

        $this->assertEquals(
            $expectedUrl,
            $actualUrl,
            'Success URL should contain correct parameters based on configuration'
        );
    }

    /**
     * Tests fail URL generation for both live and sandbox modes
     */
    public function testGetFailUrl(): void
    {
        $this->moduleSettings->expects($this->exactly(2))
            ->method('isLiveApiMode')
            ->willReturnOnConsecutiveCalls(true, false);

        // Test live mode
        $actualUrl = $this->context->getFailUrl();
        $expectedParams = [
            'cl'  => 'payment',
            'fnc' => 'provideTeleCashError'
        ];
        $expectedUrl = $this->mockShopUrl . 'index.php?' . http_build_query($expectedParams);
        $this->assertEquals(
            $expectedUrl,
            $actualUrl,
            'Fail URL should contain correct parameters in live mode'
        );

        // Test sandbox mode
        $actualUrl = $this->context->getFailUrl();
        $expectedParams['XDEBUG_SESSION_START'] = '1';
        $expectedUrl = $this->mockShopUrl . 'index.php?' . http_build_query($expectedParams);
        $this->assertEquals(
            $expectedUrl,
            $actualUrl,
            'Fail URL should contain debug parameters in sandbox mode'
        );
    }

    /**
     * Provides test cases for success URL generation
     *
     * @return array<string, array{bool, bool, bool, array<string, string|int>}>
     */
    public static function successUrlConfigProvider(): array
    {
        return [
            'no_agreements_live' => [
                false, // hasAGB
                false, // hasDownloadAgreement
                false, // hasIntangibleAgreement
                [],    // expectedParams,
                true
            ],
            'no_agreements_sandbox' => [
                false,
                false,
                false,
                [],
                false
            ],
            'only_agb_live' => [
                true,
                false,
                false,
                ['ord_agb' => 1],
                true
            ],
            'only_agb_sandbox' => [
                true,
                false,
                false,
                ['ord_agb' => 1],
                false
            ],
            'all_agreements_live' => [
                true,
                true,
                true,
                [
                    'ord_agb' => 1,
                    'oxdownloadableproductsagreement' => '1',
                    'oxserviceproductsagreement' => '1'
                ],
                true
            ],
            'all_agreements_sandbox' => [
                true,
                true,
                true,
                [
                    'ord_agb' => 1,
                    'oxdownloadableproductsagreement' => '1',
                    'oxserviceproductsagreement' => '1'
                ],
                false
            ],
            'download_agreement_only_live' => [
                false,
                true,
                false,
                ['oxdownloadableproductsagreement' => '1'],
                true
            ],
            'download_agreement_only_sandbox' => [
                false,
                true,
                false,
                ['oxdownloadableproductsagreement' => '1'],
                false
            ],
            'intangible_agreement_only_live' => [
                false,
                false,
                true,
                ['oxserviceproductsagreement' => '1'],
                true
            ],
            'intangible_agreement_only_sandbox' => [
                false,
                false,
                true,
                ['oxserviceproductsagreement' => '1'],
                false
            ]
        ];
    }

    /**
     * Tests notification URL generation for different API modes
     *
     * @dataProvider notificationUrlModeProvider
     */
    public function testGetNotificationUrl(bool $isLiveMode, array $expectedParams): void
    {
        $this->moduleSettings->expects($this->once())
            ->method('isLiveApiMode')
            ->willReturn($isLiveMode);

        $actualUrl = $this->context->getNotificationUrl();

        $baseParams = [
            'cl' => 'FrontendTeleCashNotificationEndpoint',
            'fnc' => 'receiveNotifications'
        ];
        $expectedParams = array_merge($baseParams, $expectedParams);
        $expectedUrl = $this->mockShopUrl . 'index.php?' . http_build_query($expectedParams);

        $this->assertEquals(
            $expectedUrl,
            $actualUrl,
            'Notification URL should contain correct parameters based on API mode'
        );
    }

    /**
     * Provides test cases for notification URL generation
     *
     * @return array<string, array{bool, array<string, string|int>}>
     */
    public static function notificationUrlModeProvider(): array
    {
        return [
            'live_mode' => [
                true,  // isLiveMode
                []     // expectedParams
            ],
            'sandbox_mode' => [
                false,
                ['XDEBUG_SESSION_START' => '1']
            ]
        ];
    }

    /**
     * Provides test cases for different dates
     *
     * Tests various date scenarios:
     * - Year boundaries
     * - Leap year dates
     * - Mid-year dates
     *
     * @return array<string, array<string, string>> Array of test cases
     */
    public static function dateProvider(): array
    {
        return [
            'start_of_year' => [
                'testDate' => '2024-01-01',
                'expectedDate' => '2024-01-01'
            ],
            'end_of_year' => [
                'testDate' => '2024-12-31',
                'expectedDate' => '2024-12-31'
            ],
            'leap_year_day' => [
                'testDate' => '2024-02-29',
                'expectedDate' => '2024-02-29'
            ],
            'mid_year' => [
                'testDate' => '2024-06-15',
                'expectedDate' => '2024-06-15'
            ]
        ];
    }
}
