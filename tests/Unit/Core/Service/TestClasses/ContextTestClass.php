<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\Core\Service\TestClasses;

use OxidSolutionCatalysts\TeleCash\Core\Service\Context;

/**
 * Test-specific extension of Context class
 *
 * This class extends the base Context class to enable controlled testing of
 * date-dependent functionality. It allows tests to set specific dates without
 * relying on the system clock, ensuring consistent and reliable test results.
 *
 * Features:
 * - Overridable date functionality
 * - Default fallback to system date
 * - Maintains original Context behavior when no fixed date is set
 *
 * Usage example:
 * ```php
 * $context = new ContextTestClass($config);
 * $context->setFixedDate('2024-01-15');
 * $path = $context->getTeleCashLogFilePath();
 * ```
 *
 * @package OxidSolutionCatalysts\TeleCash\Tests\Unit\Core\Service\TestClasses
 */
class ContextTestClass extends Context
{
    /**
     * Stores the fixed date for testing
     * When null, falls back to current system date
     *
     * @var string|null Date in Y-m-d format or null for system date
     */
    private ?string $fixedDate = null;

    /**
     * Sets a fixed date to be used in tests
     *
     * This method allows tests to control the date that will be used
     * for log file naming. This enables testing of date-dependent
     * functionality without relying on the system clock.
     *
     * @param string|null $date Date in Y-m-d format or null to use current date
     *                         Example: '2024-01-15'
     */
    public function setFixedDate(?string $date): void
    {
        $this->fixedDate = $date;
    }

    /**
     * Returns either the fixed test date or the current system date
     *
     * Overrides the parent's getCurrentDate method to enable controlled
     * testing. If no fixed date is set, falls back to the parent's
     * implementation using the system date.
     *
     * @return string Date in Y-m-d format
     */
    protected function getCurrentDate(): string
    {
        return $this->fixedDate ?? date('Y-m-d');
    }
}
