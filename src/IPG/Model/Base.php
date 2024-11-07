<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\IPG\Connect\Model;

/**
 * Base class for handling field-value mappings
 */
class Base
{
    /**
     * List of available field names
     *
     * @var array<int, string>
     */
    protected array $fields = [];

    /**
     * Field values storage
     *
     * @var array<string, string>
     */
    protected array $values = [];

    /**
     * Parses data from an array into the values storage based on defined fields
     *
     * @param array<string, string> $data Input data array to parse
     */
    public function parseFromArray(array $data): void
    {
        foreach ($this->fields as $field) {
            if (array_key_exists($field, $data)) {
                $this->values[$field] = $data[$field];
            }
        }
    }

    /**
     * Returns all stored field values as array
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return $this->values;
    }
}
