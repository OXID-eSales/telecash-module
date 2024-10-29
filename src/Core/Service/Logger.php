<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Core\Service;

use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleSettingsServiceInterface;
use Psr\Log\LoggerInterface;

/**
 * Service Logger for TeleCash Module
 *
 * Provides logging functionality with configurable log levels.
 * Implements a PSR-3 compliant logging system with additional
 * level validation and configuration options.
 *
 * Features:
 * - Configurable log levels via shop configuration
 * - Level-based filtering of log messages
 * - Exception context support
 * - PSR-3 Logger integration
 */
class Logger
{
    /**
     * PSR-3 compliant logger instance for module logging
     *
     * @var LoggerInterface
     */
    private LoggerInterface $moduleLogger;

    /**
     * Service for accessing module configuration
     *
     * @var ModuleSettingsServiceInterface
     */
    private ModuleSettingsServiceInterface $moduleSettings;

    /**
     * Initializes the logger with required dependencies
     *
     * @param LoggerInterface $moduleLogger PSR-3 logger implementation
     * @param ModuleSettingsServiceInterface $moduleSettings Service for accessing module configuration
     */
    public function __construct(
        LoggerInterface $moduleLogger,
        ModuleSettingsServiceInterface $moduleSettings
    ) {
        $this->moduleLogger = $moduleLogger;
        $this->moduleSettings = $moduleSettings;
    }

    /**
     * Logs a message if it meets the configured minimum log level
     *
     * This method will check if the provided log level is enabled in the
     * current configuration before actually logging the message.
     *
     * @param string $level Log level (error, info, debug)
     * @param string $message Message to be logged
     * @param array<string, mixed> $exception Optional exception context
     * @return void
     */
    public function log(string $level, string $message, array $exception = []): void
    {
        if ($this->isLogLevel($level)) {
            $this->moduleLogger->$level($message, $exception);
        }
    }

    /**
     * Checks if a given log level should be processed
     *
     * Compares the provided level against the configured minimum log level.
     * Handles invalid levels by defaulting to 'error'.
     *
     * Logic:
     * - Retrieves configured log level from module config
     * - Validates both configured and provided levels
     * - Returns true if provided level should be logged
     *
     * @param string $level Log level to check
     * @return bool True if the level should be logged
     */
    public function isLogLevel(string $level): bool
    {
        $logLevel = $this->moduleSettings->getLogLevel() ?:
            ModuleSettingsServiceInterface::LOG_LEVEL_ERROR;
        $logLevel = isset(ModuleSettingsServiceInterface::TELECASH_LOG_LEVELS[$logLevel]) ?
            $logLevel :
            ModuleSettingsServiceInterface::LOG_LEVEL_ERROR;
        $level = isset(ModuleSettingsServiceInterface::TELECASH_LOG_LEVELS[$level]) ?
            $level :
            ModuleSettingsServiceInterface::LOG_LEVEL_ERROR;
        return ModuleSettingsServiceInterface::TELECASH_LOG_LEVELS[$logLevel] <=
            ModuleSettingsServiceInterface::TELECASH_LOG_LEVELS[$level];
    }
}
