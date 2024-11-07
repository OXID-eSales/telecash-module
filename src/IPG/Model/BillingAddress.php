<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\IPG\Connect\Model;

/**
 * BillingAddress class for handling field-value mappings
 */
class BillingAddress extends Base
{
    /**
     * List of available BillingAddress field names
     *
     * @var array<int, string>
     */
    protected array $fields = [
        'bcompany', 'bname', 'baddr1', 'baddr2', 'bcity', 'bstate', 'bcountry', 'bzip', 'phone', 'fax', 'email'
    ];

    /**
     * BillingAddress constructor.
     * The field "bname" is mandatory and will be used as the card holder account name or SEPA bank account holder name.
     *
     * @param array<string, string> $data Input data array to parse
     */
    public function __construct(array $data)
    {
        $this->parseFromArray($data);
    }
}
