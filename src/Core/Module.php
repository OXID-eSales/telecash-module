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
    public const TELECASH_PAYMENT_EXTENSION_TABLE_OXPAYMENTID = 'oxpaymentid';
    public const TELECASH_PAYMENT_EXTENSION_TABLE_IDENT = 'telecashident';
    public const TELECASH_PAYMENT_EXTENSION_TABLE_CAPTURETYPE = 'telecashcapturetype';
    public const TELECASH_DB_FIELD_IDENT = self::TELECASH_PAYMENT_EXTENSION_TABLE .
        '__' . self::TELECASH_PAYMENT_EXTENSION_TABLE_IDENT;
    public const TELECASH_DB_FIELD_CAPTURETYPE = self::TELECASH_PAYMENT_EXTENSION_TABLE .
    '__' . self::TELECASH_PAYMENT_EXTENSION_TABLE_CAPTURETYPE;

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

    public const TELECASH_CAPTURE_TYPES = [
        self::TELECASH_PAYMENT_IDENT_DEFAULT => [
            self::TELECASH_CAPTURE_TYPE_DIRECT
        ],
        self::TELECASH_PAYMENT_IDENT_CREDITCARD => [
            self::TELECASH_CAPTURE_TYPE_DIRECT,
            self::TELECASH_CAPTURE_TYPE_ONDELIVERY,
            self::TELECASH_CAPTURE_TYPE_MANUALLY
        ],
        self::TELECASH_PAYMENT_IDENT_PAYPAL => [
            self::TELECASH_CAPTURE_TYPE_DIRECT,
            self::TELECASH_CAPTURE_TYPE_ONDELIVERY,
            self::TELECASH_CAPTURE_TYPE_MANUALLY
        ],
        self::TELECASH_PAYMENT_IDENT_SEPA => [
            self::TELECASH_CAPTURE_TYPE_DIRECT
        ],
        self::TELECASH_PAYMENT_IDENT_SOFORT => [
            self::TELECASH_CAPTURE_TYPE_DIRECT
        ]
    ];
}
