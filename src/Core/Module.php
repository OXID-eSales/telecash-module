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

    public const TELECASH_ORDER_EXTENSION_TABLE = 'osc_telecash_order';
    public const TELECASH_ORDER_EXTENSION_TABLE_OXORDERID = 'oxorderid';
    public const TELECASH_ORDER_EXTENSION_TABLE_OID = 'oid';
    public const TELECASH_ORDER_EXTENSION_TABLE_STATUS = 'status';
    public const TELECASH_ORDER_EXTENSION_TABLE_CURRENCY = 'currency';
    public const TELECASH_ORDER_EXTENSION_TABLE_TXNTYPE = 'txntype';
    public const TELECASH_ORDER_EXTENSION_TABLE_PAYMENTMETHOD = 'paymentmethod';
    public const TELECASH_ORDER_EXTENSION_TABLE_CHARGETOTAL = 'chargetotal';
    public const TELECASH_ORDER_HISTORY_EXTENSION_TABLE = 'osc_telecash_order_history';
    public const TELECASH_ORDER_HISTORY_EXTENSION_TABLE_OID = 'oxorderid';
    public const TELECASH_ORDER_HISTORY_EXTENSION_TABLE_RESPONSE = 'telecashresponse';


    public const TELECASH_PAYMENT_IDENT_DEFAULT = 'none';
    public const TELECASH_PAYMENT_IDENT_TELECASH = 'telecash';
    public const TELECASH_PAYMENT_IDENT_CC_AMERICAN = 'cc_american';
    public const TELECASH_PAYMENT_IDENT_CC_VISA = 'cc_visa';
    public const TELECASH_PAYMENT_IDENT_CC_MASTERCARD = 'cc_mastercard';

    public const TELECASH_PAYMENT_IDENT_PAYPAL = 'paypal';
    public const TELECASH_PAYMENT_IDENT_SEPA = 'sepa';

    public const TELECASH_PAYMENT_IDENTS = [
        self::TELECASH_PAYMENT_IDENT_DEFAULT       => '',
        self::TELECASH_PAYMENT_IDENT_TELECASH      => '',
        self::TELECASH_PAYMENT_IDENT_CC_AMERICAN   => 'A',
        self::TELECASH_PAYMENT_IDENT_CC_MASTERCARD => 'M',
        self::TELECASH_PAYMENT_IDENT_CC_VISA       => 'V',
        self::TELECASH_PAYMENT_IDENT_SEPA          => 'debitDE',
        self::TELECASH_PAYMENT_IDENT_PAYPAL        => 'paypal',
    ];

    public const TELECASH_CAPTURE_TYPE_DIRECT = 'direct';
    public const TELECASH_CAPTURE_TYPE_ONDELIVERY = 'ondelivery';
    public const TELECASH_CAPTURE_TYPE_MANUALLY = 'manually';

    public const TELECASH_CAPTURE_TYPES = [
        self::TELECASH_PAYMENT_IDENT_DEFAULT => [
            self::TELECASH_CAPTURE_TYPE_DIRECT
        ],
        self::TELECASH_PAYMENT_IDENT_TELECASH => [
            self::TELECASH_CAPTURE_TYPE_DIRECT
        ],
        self::TELECASH_PAYMENT_IDENT_CC_AMERICAN => [
            self::TELECASH_CAPTURE_TYPE_DIRECT,
            self::TELECASH_CAPTURE_TYPE_ONDELIVERY,
            self::TELECASH_CAPTURE_TYPE_MANUALLY
        ],
        self::TELECASH_PAYMENT_IDENT_CC_VISA => [
            self::TELECASH_CAPTURE_TYPE_DIRECT,
            self::TELECASH_CAPTURE_TYPE_ONDELIVERY,
            self::TELECASH_CAPTURE_TYPE_MANUALLY
        ],
        self::TELECASH_PAYMENT_IDENT_CC_MASTERCARD => [
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
    ];

    public const TELECASH_TXN_TYPE_SALE = 'sale';
    public const TELECASH_TXN_TYPE_POSTAUTH = 'preauth';

    public const TELECASH_TRANSACTION_TYPES = [
        self::TELECASH_CAPTURE_TYPE_DIRECT     => self::TELECASH_TXN_TYPE_SALE,
        self::TELECASH_CAPTURE_TYPE_ONDELIVERY => self::TELECASH_TXN_TYPE_POSTAUTH,
        self::TELECASH_CAPTURE_TYPE_MANUALLY   => self::TELECASH_TXN_TYPE_POSTAUTH
    ];
}
