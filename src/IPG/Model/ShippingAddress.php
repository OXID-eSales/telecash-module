<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\IPG\Model;

/**
 * ShippingAddress class for handling field-value mappings
 */
class ShippingAddress extends Base
{
    /**
     * List of available ShippingAddress field names
     *
     * @var array<int, string>
     */
    protected array $fields = [
        'sname', 'saddr1', 'saddr2', 'scity', 'sstate', 'scountry', 'szip'
    ];

    /**
     * DirectDebitData constructor.
     *
     * @param array<string, string> $data Input data array to parse
     */

    public function __construct(array $data)
    {
        $this->parseFromArray($data);
    }
}
