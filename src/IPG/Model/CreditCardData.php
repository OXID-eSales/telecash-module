<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\IPG\Connect\Model;

/**
 * CreditCardData class for handling field-value mappings
 */
class CreditCardData extends Base
{
    /**
     * List of available CreditCardData field names
     *
     * @var array<int, string>
     */
    protected array $fields = [
        'ccbrand', 'ccbin', 'cccountry', 'expmonth', 'expyear', 'cardnumber',
        'cardLastFourDigits', 'fundingCardNumberBin', 'fundingCardNumberLast4'
    ];

    /**
     * CreditCardData constructor.
     *
     * @param array<string, string> $data Input data array to parse
     */
    public function __construct(array $data)
    {
        $this->parseFromArray($data);
    }
}
