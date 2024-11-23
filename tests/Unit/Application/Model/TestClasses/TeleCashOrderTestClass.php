<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\Application\Model\TestClasses;

use OxidSolutionCatalysts\TeleCash\Application\Model\TeleCashOrder;
use OxidSolutionCatalysts\TeleCash\IPG\Model\TransactionResult;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashCurrency;

/**
 * Test Class for TeleCashOrder
 *
 * This class extends TeleCashOrder and overrides database-related functionality
 * to enable isolated unit testing without database dependencies.
 * It provides a clean testing environment by:
 * - Skipping parent constructor initialization
 * - Overriding database operations
 * - Maintaining only essential functionality
 */
class TeleCashOrderTestClass extends TeleCashOrder
{
    /**
     * Currency handling service, made protected for testing
     */
    protected TeleCashCurrency $teleCashCurrency;

    /**
     * Transaction result container, made protected for testing
     */
    protected TransactionResult $transactionResult;

    /**
     * Simplified constructor for testing purposes
     *
     * Initializes only the essential components needed for testing:
     * - Order ID
     * - Currency handler
     * - Empty transaction result
     *
     * @param string $oxOrderId The order ID to be used in tests
     */
    public function __construct(string $oxOrderId)
    {
        // Skip parent constructor to avoid database operations
        $this->oxOrderId = $oxOrderId;
        $this->teleCashCurrency = new TeleCashCurrency();
        $this->transactionResult = new TransactionResult([]);
    }

    /**
     * Override database table initialization
     *
     * @param string|null $tableName Unused in test context
     * @param bool $forceAllFields Unused in test context
     */
    public function init($tableName = null, $forceAllFields = false): void
    {
        // Disable database initialization
    }

    /**
     * Override database field addition
     *
     * @param string $fieldName Unused in test context
     * @param mixed $fieldStatus Unused in test context
     * @param mixed $type Unused in test context
     * @param mixed $length Unused in test context
     */
    protected function addField($fieldName, $fieldStatus = null, $type = null, $length = null)
    {
        // Disable field operations
    }

    /**
     * Override database record assignment
     *
     * @param mixed $dbRecord Unused in test context
     * @return bool Always returns true in test context
     */
    public function assign($dbRecord)
    {
        // Disable database assignments
        return true;
    }
}
