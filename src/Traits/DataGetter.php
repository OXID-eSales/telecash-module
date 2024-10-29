<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Traits;

/**
 * Convenience trait to work with DataGetter.
 */
trait DataGetter
{
    /**
     * Checks if the field value is a string.
     *
     * @return string If no string, then empty string will be returned
     */
    public function getFieldStringData(string $key): string
    {
        $value = $this->getFieldData($key);
        return is_string($value) ? $value : '';
    }

    /**
     * Checks if the field value is a float.
     *
     * @return float If no float, then 0.0 will be returned
     */
    public function getFieldFloatData(string $key): float
    {
        $value = $this->getFieldData($key);
        return is_numeric($value) ? (float) $value : 0.0;
    }

    /**
     * Checks if the field value is a integer.
     *
     * @return int If no integer, then 0 will be returned
     */
    public function getFieldIntData(string $key): int
    {
        $value = $this->getFieldData($key);
        return is_numeric($value) ? (int) $value : 0;
    }

    /**
     * Checks if the field value is bool.
     *
     * @return bool If not a bool, then false will be returned
     */
    public function getFieldBoolData(string $key): bool
    {
        $value = $this->getFieldData($key);
        return isset($value) && $value;
    }

    /**
     * Checks if the field value is a string.
     *
     * @return string If no string, then empty string will be returned
     */
    public function getFieldRawStringData(string $key): string
    {
        $value = $this->getRawFieldData($key);
        return is_string($value) ? $value : '';
    }
}
