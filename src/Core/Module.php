<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Core;

final class Module
{
    public const MODULE_ID = 'osc_telecash';

    public const TELECASH_PAYMENT_EXTENSION_TABLE = 'osc_telecash_payment';


    public const TELECASH_PAYMENT_IDENT_DEFAULT = 'none';
    public const TELECASH_PAYMENT_IDENT_CREDITCARD = 'creditcard';
    public const TELECASH_PAYMENT_IDENT_PAYPAL = 'paypal';
    public const TELECASH_PAYMENT_IDENT_SEPA = 'sepa';
    public const TELECASH_PAYMENT_IDENT_SOFORT = 'sofort';

    public const TELECASH_PAYMENT_IDENTS = [
        self::TELECASH_PAYMENT_IDENT_DEFAULT,
        self::TELECASH_PAYMENT_IDENT_CREDITCARD,
        self::TELECASH_PAYMENT_IDENT_PAYPAL,
        self::TELECASH_PAYMENT_IDENT_SEPA,
        self::TELECASH_PAYMENT_IDENT_SOFORT,
    ];


    public const TELECASH_CAPTURE_TYPE_DIRECT = 'direct';
    public const TELECASH_CAPTURE_TYPE_ONDELIVERY = 'ondelivery';
    public const TELECASH_CAPTURE_TYPE_MANUALLY = 'manually';

    public const TELECASH_CAPTURE_CREDITCARD_TYPES = [
        self::TELECASH_CAPTURE_TYPE_DIRECT,
        self::TELECASH_CAPTURE_TYPE_ONDELIVERY,
        self::TELECASH_CAPTURE_TYPE_MANUALLY
    ];

    public const TELECASH_CAPTURE_PAYPAL_TYPES = [
        self::TELECASH_CAPTURE_TYPE_DIRECT,
        self::TELECASH_CAPTURE_TYPE_ONDELIVERY,
        self::TELECASH_CAPTURE_TYPE_MANUALLY
    ];

    public const TELECASH_CAPTURE_SEPA_TYPES = [
        self::TELECASH_CAPTURE_TYPE_DIRECT
    ];

    public const TELECASH_CAPTURE_SOFORT_TYPES = [
        self::TELECASH_CAPTURE_TYPE_DIRECT
    ];
}
