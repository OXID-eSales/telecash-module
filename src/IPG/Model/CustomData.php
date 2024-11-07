<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\IPG\Model;

/**
 * CustomData class for handling field-value mappings
 */
class CustomData extends Base
{
    /**
     * CustomData constructor.
     *
     * @param array<string, string> $data Input data array to parse
     */
    public function __construct(array $data)
    {
        foreach (array_keys($data) as $key) {
            if (str_starts_with($key, 'customParam_')) {
                $this->fields[] = $key;
            }
        }
        $this->parseFromArray($data);
    }
}
