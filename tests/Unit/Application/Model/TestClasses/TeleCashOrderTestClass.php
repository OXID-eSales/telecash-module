<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\Application\Model\TestClasses;

use Doctrine\DBAL\Connection;
use OxidSolutionCatalysts\TeleCash\Application\Model\TeleCashOrder;
use OxidSolutionCatalysts\TeleCash\IPG\Model\TransactionResult;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashCurrency;
use Psr\Container\ContainerInterface;

/**
 * Test Double for TeleCashOrder
 *
 * Provides a testable version of TeleCashOrder that:
 * - Works without database connection
 * - Skips framework initialization
 * - Simulates data loading/saving
 * - Allows testing both data sources
 */
class TeleCashOrderTestClass extends TeleCashOrder
{
    /**
     * Simulated database storage
     *
     * @var array
     */
    private array $dbData = [];

    /**
     * Indicates if data was "loaded" from database
     *
     * @var bool
     */
    private bool $isLoaded = false;

    protected TransactionResult $transactionResult;

    /**
     * Constructor that skips framework initialization
     *
     * @param Connection|null $connection Optional database connection (unused)
     * @param bool $initParent Whether to initialize parent (unused)
     */
    public function __construct(?Connection $connection = null, bool $initParent = true)
    {
        // Skip all parent initialization
        $this->teleCashCurrency = new TeleCashCurrency();
        $this->transactionResult = new TransactionResult([]);
    }

    // Mock container methods
    protected function getContainer(): ContainerInterface
    {
        return new class implements ContainerInterface {
            public function get(string $id)
            {
                return null;
            }

            public function has(string $id): bool
            {
                return false;
            }
        };
    }

    /**
     * Simulates loading data from database
     *
     * Sets the provided data as if it was loaded from the database
     * and marks the instance as loaded.
     *
     * @param array $data The data to simulate as database content
     * @return void
     */
    public function simulateLoadedFromDb(array $data): void
    {
        $this->dbData = $data;
        $this->isLoaded = true;
    }

    public function isLoaded(): bool
    {
        return $this->isLoaded;
    }

    /**
     * Simulates database field retrieval for string values
     *
     * @param string $fieldName The field name to retrieve
     * @return string The field value or empty string if not found
     */
    public function getFieldStringData($fieldName): string
    {
        return (string)($this->dbData[$fieldName] ?? '');
    }

    /**
     * Simulates database field retrieval for float values
     *
     * @param string $fieldName The field name to retrieve
     * @return float The field value or 0.0 if not found
     */
    public function getFieldFloatData($fieldName): float
    {
        return (float)($this->dbData[$fieldName] ?? 0.0);
    }
}
