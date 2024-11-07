<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\IPG\Model;

/**
 * DirectDebitData class for handling field-value mappings
 */
class DirectDebitData extends Base
{
    /**
     * List of available DirectDebitData field names
     *
     * @var array<int, string>
     */
    protected array $fields = [
        'mandateReference', 'mandateDate', 'mandateType', 'mandateUrl', 'iban', 'bic',
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
