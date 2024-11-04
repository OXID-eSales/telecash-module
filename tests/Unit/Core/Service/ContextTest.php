<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\Core\Service;

use OxidEsales\Eshop\Core\Config;
use OxidSolutionCatalysts\TeleCash\Core\Module;
use OxidSolutionCatalysts\TeleCash\Tests\Unit\Core\Service\TestClasses\ContextTestClass;
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
    /**
     * Mock for shop configuration
     * @var Config&MockObject
     */
    private Config&MockObject $shopConfig;

    /**
     * Test-specific context implementation
     * @var ContextTestClass
     */
    private ContextTestClass $context;

    /**
     * Base directory for log files in test environment
     * @var string
     */
    private string $mockLogsDir = '/var/www/shop/source/log';

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

        $this->context = new ContextTestClass($this->shopConfig);
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
     */
    public function testGetTeleCashLogFilePathWithDifferentDirectories(
        string $baseDir,
        string $expectedPrefix
    ): void {
        $testDate = '2024-01-15';

        $shopConfig = $this->createMock(Config::class);
        $shopConfig->method('getLogsDir')
            ->willReturn($baseDir);

        $context = new ContextTestClass($shopConfig);
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
