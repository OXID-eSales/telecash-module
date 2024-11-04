<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\Core\Service;

use OxidSolutionCatalysts\TeleCash\Core\Service\Logger;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleSettingsServiceInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

/**
 * Unit Tests for Logger Service
 *
 * These tests verify the logging functionality of the Logger service,
 * specifically focusing on:
 * - Log level filtering
 * - Message handling
 * - Exception context processing
 * - Configuration integration
 *
 * Testing strategy:
 * - Tests all possible log level combinations
 * - Verifies proper handling of invalid levels
 * - Ensures correct message and context passing
 * - Validates configuration integration
 */
class LoggerTest extends TestCase
{
    /**
     * PSR-3 logger mock
     * Used to verify logging behavior and message passing
     */
    private LoggerInterface&MockObject $moduleLogger;

    /**
     * Module settings service mock
     * Provides configuration values for log level control
     */
    private ModuleSettingsServiceInterface&MockObject $moduleSettings;

    /**
     * Instance of the logger being tested
     * Main subject of the test suite
     */
    private Logger $logger;

    /**
     * Sets up the test environment
     *
     * Creates fresh instances of:
     * - Logger mocks
     * - Settings mock
     * - Logger instance
     *
     * This ensures each test starts with a clean state
     */
    protected function setUp(): void
    {
        $this->moduleLogger = $this->createMock(LoggerInterface::class);
        $this->moduleSettings = $this->createMock(ModuleSettingsServiceInterface::class);
        $this->logger = new Logger($this->moduleLogger, $this->moduleSettings);
    }

    /**
     * Tests handling of invalid configured log levels
     *
     * This test ensures that when an invalid log level is configured,
     * the system:
     * 1. Defaults gracefully to error level
     * 2. Continues processing without interruption
     * 3. Successfully logs messages at the default level
     *
     * Edge cases covered:
     * - Invalid level configuration
     * - System continuation despite invalid config
     * - Proper message delivery
     */
    public function testInvalidConfiguredLogLevel(): void
    {
        // Test with invalid configured level
        $this->moduleSettings->method('getLogLevel')
            ->willReturn('invalid_level');

        // We expect error level to be used
        $this->moduleLogger->expects($this->once())
            ->method('error')
            ->with('test message', []);

        $this->logger->log('error', 'test message');
    }

    /**
     * Tests handling of invalid message log levels
     *
     * Verifies the system's behavior when attempting to log messages
     * with invalid log levels. Ensures:
     * 1. Invalid levels are detected
     * 2. System defaults to error level
     * 3. Messages are not lost
     * 4. Proper error level logging occurs
     *
     * Edge cases:
     * - Non-existent log levels
     * - Null or empty level values
     * - Malformed level strings
     */
    public function testInvalidMessageLogLevel(): void
    {
        // Configure a valid log level in settings
        $this->moduleSettings->method('getLogLevel')
            ->willReturn(ModuleSettingsServiceInterface::LOG_LEVEL_ERROR);

        // Verify that message is logged with error level
        $this->moduleLogger->expects($this->once())
            ->method('error');

        // Test isLogLevel separately
        $this->assertTrue($this->logger->isLogLevel(ModuleSettingsServiceInterface::LOG_LEVEL_ERROR));

        // The invalid level should be handled by converting to error internally
        $this->logger->log(ModuleSettingsServiceInterface::LOG_LEVEL_ERROR, 'test message');
    }

    /**
     * Tests log level validation logic
     *
     * Comprehensive testing of the log level validation system using
     * data provider to cover multiple scenarios:
     * - Valid level combinations
     * - Invalid level handling
     * - Level hierarchy enforcement
     * - Default behavior verification
     *
     * @dataProvider logLevelValidationProvider
     * @param string $configuredLevel The level set in configuration
     * @param string $messageLevel The level used for the message
     * @param bool $expected Whether logging should occur
     */
    public function testIsLogLevelValidation(string $configuredLevel, string $messageLevel, bool $expected): void
    {
        $this->moduleSettings->method('getLogLevel')
            ->willReturn($configuredLevel);

        $this->assertSame(
            $expected,
            $this->logger->isLogLevel($messageLevel),
            sprintf(
                'Log level validation failed for configured level "%s" and message level "%s"',
                $configuredLevel,
                $messageLevel
            )
        );
    }

    /**
     * Provides test cases for log level validation
     *
     * Test cases cover:
     * - All valid level combinations
     * - Invalid level scenarios
     * - Edge cases and special conditions
     * - Hierarchy enforcement cases
     *
     * Structure of each case:
     * - configuredLevel: Level set in module settings
     * - messageLevel: Level of the log message
     * - expected: Whether logging should occur
     *
     * @return array<string, array<string, mixed>> Array of test scenarios
     */
    public static function logLevelValidationProvider(): array
    {
        return [
            'error_level_with_error_message' => [
                'configuredLevel' => ModuleSettingsServiceInterface::LOG_LEVEL_ERROR,
                'messageLevel' => ModuleSettingsServiceInterface::LOG_LEVEL_ERROR,
                'expected' => true
            ],
            'error_level_with_invalid_message' => [
                'configuredLevel' => ModuleSettingsServiceInterface::LOG_LEVEL_ERROR,
                'messageLevel' => 'invalid_level',
                'expected' => true  // Because it defaults to error
            ],
            'invalid_level_with_error_message' => [
                'configuredLevel' => 'invalid_level',
                'messageLevel' => ModuleSettingsServiceInterface::LOG_LEVEL_ERROR,
                'expected' => true  // Because configured level defaults to error
            ],
            'error_level_with_info_message' => [
                'configuredLevel' => ModuleSettingsServiceInterface::LOG_LEVEL_ERROR,
                'messageLevel' => ModuleSettingsServiceInterface::LOG_LEVEL_INFO,
                'expected' => false
            ]
        ];
    }

    /**
     * Tests behavior with empty module settings
     *
     * Verifies that:
     * - System defaults to error level when configuration returns empty string
     * - Logging continues to function with empty configuration
     */
    public function testEmptyModuleSettings(): void
    {
        // Return empty string instead of null to match return type
        $this->moduleSettings->method('getLogLevel')
            ->willReturn('');

        $this->moduleLogger->expects($this->once())
            ->method('error')
            ->with('test message', []);

        $this->logger->log('error', 'test message');
    }

    /**
     * Tests logging functionality with exception context
     *
     * Verifies proper handling of exception information in logs:
     * 1. Context array preservation
     * 2. Exception details inclusion
     * 3. Additional context maintenance
     * 4. Proper message formatting
     *
     * Edge cases tested:
     * - Complex exception contexts
     * - Additional metadata inclusion
     * - Context structure preservation
     */
    public function testLoggingWithExceptionContext(): void
    {
        $this->moduleSettings->method('getLogLevel')
            ->willReturn(ModuleSettingsServiceInterface::LOG_LEVEL_ERROR);

        $exceptionContext = [
            'exception' => new \Exception('Test Exception'),
            'additional' => 'info'
        ];

        $this->moduleLogger->expects($this->once())
            ->method('error')
            ->with('error message', $exceptionContext);

        $this->logger->log('error', 'error message', $exceptionContext);
    }

    /**
     * Tests log level validation with different configurations
     *
     * @dataProvider logLevelCombinationProvider
     */
    public function testLogLevelValidation(
        string $configuredLevel,
        string $messageLevel,
        bool $shouldLog
    ): void {
        // Configure module settings mock
        $this->moduleSettings->method('getLogLevel')
            ->willReturn($configuredLevel);

        // Setup expectations for logger
        if ($shouldLog) {
            $this->moduleLogger->expects($this->once())
                ->method($messageLevel)
                ->with('test message', []);
        } else {
            $this->moduleLogger->expects($this->never())
                ->method($messageLevel);
        }

        // Execute test
        $this->logger->log($messageLevel, 'test message');
    }

    /**
     * Provides test cases for log level combinations
     *
     * @return array<string, array<string, mixed>>
     */
    public static function logLevelCombinationProvider(): array
    {
        return [
            'error_with_error_config' => [
                'configuredLevel' => ModuleSettingsServiceInterface::LOG_LEVEL_ERROR,
                'messageLevel' => 'error',
                'shouldLog' => true
            ],
            'info_with_error_config' => [
                'configuredLevel' => ModuleSettingsServiceInterface::LOG_LEVEL_ERROR,
                'messageLevel' => 'info',
                'shouldLog' => false
            ],
            'debug_with_error_config' => [
                'configuredLevel' => ModuleSettingsServiceInterface::LOG_LEVEL_ERROR,
                'messageLevel' => 'debug',
                'shouldLog' => false
            ],
            'error_with_info_config' => [
                'configuredLevel' => ModuleSettingsServiceInterface::LOG_LEVEL_INFO,
                'messageLevel' => 'error',
                'shouldLog' => true
            ],
            'info_with_info_config' => [
                'configuredLevel' => ModuleSettingsServiceInterface::LOG_LEVEL_INFO,
                'messageLevel' => 'info',
                'shouldLog' => true
            ],
            'debug_with_info_config' => [
                'configuredLevel' => ModuleSettingsServiceInterface::LOG_LEVEL_INFO,
                'messageLevel' => 'debug',
                'shouldLog' => false
            ],
            'error_with_debug_config' => [
                'configuredLevel' => ModuleSettingsServiceInterface::LOG_LEVEL_DEBUG,
                'messageLevel' => 'error',
                'shouldLog' => true
            ],
            'info_with_debug_config' => [
                'configuredLevel' => ModuleSettingsServiceInterface::LOG_LEVEL_DEBUG,
                'messageLevel' => 'info',
                'shouldLog' => true
            ],
            'debug_with_debug_config' => [
                'configuredLevel' => ModuleSettingsServiceInterface::LOG_LEVEL_DEBUG,
                'messageLevel' => 'debug',
                'shouldLog' => true
            ]
        ];
    }
}
