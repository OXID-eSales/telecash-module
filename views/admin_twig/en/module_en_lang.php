<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

use OxidSolutionCatalysts\TeleCash\Core\Module;

$aLang = [
    'charset' => 'UTF-8',

    'tbclorder_telecash' => 'TeleCash',

    'OSC_TELECASH_PAYMENT_DATA_INITIAL_ERROR'                                      => 'An error occurred during the initial setup of the payment method:',
    'OSC_TELECASH_PAYMENT_IDENT'                                                   => 'Telecash Payment Method',
    'OSC_TELECASH_PAYMENT_IDENT_' . Module::TELECASH_PAYMENT_IDENT_DEFAULT         => 'no TeleCash Payment',
    'OSC_TELECASH_PAYMENT_IDENT_' . Module::TELECASH_PAYMENT_IDENT_TELECASH        => 'TeleCash Connect',
    'OSC_TELECASH_PAYMENT_IDENT_' . Module::TELECASH_PAYMENT_IDENT_CC_AMERICAN     => 'Credit Card American Express',
    'OSC_TELECASH_PAYMENT_IDENT_' . Module::TELECASH_PAYMENT_IDENT_CC_MASTERCARD   => 'Credit Card Mastercard',
    'OSC_TELECASH_PAYMENT_IDENT_' . Module::TELECASH_PAYMENT_IDENT_CC_VISA         => 'Credit Card Visa',
    'OSC_TELECASH_PAYMENT_IDENT_' . Module::TELECASH_PAYMENT_IDENT_PAYPAL          => 'PayPal',
    'OSC_TELECASH_PAYMENT_IDENT_' . Module::TELECASH_PAYMENT_IDENT_SEPA            => 'SEPA',
    'OSC_TELECASH_TXNTYPE_' . Module::TELECASH_TXN_TYPE_SALE                       => 'Collect',
    'OSC_TELECASH_TXNTYPE_' . Module::TELECASH_TXN_TYPE_POSTAUTH                   => 'Authorize',
    'HELP_OSC_TELECASH_PAYMENT_IDENT'                                              => 'This payment method can be set up as a TeleCash payment method. The options Credit Card, Instant Bank Transfer, PayPal and SEPA Direct Debit are available',
    'OSC_TELECASH_PAYMENT_CAPTURETYPE'                                             => 'TeleCash Capture Time',
    'OSC_TELECASH_PAYMENT_CAPTURETYPE_' . Module::TELECASH_CAPTURE_TYPE_DIRECT     => 'Direct',
    'OSC_TELECASH_PAYMENT_CAPTURETYPE_' . Module::TELECASH_CAPTURE_TYPE_ONDELIVERY => 'Automatically on Delivery',
    'OSC_TELECASH_PAYMENT_CAPTURETYPE_' . Module::TELECASH_CAPTURE_TYPE_MANUALLY   => 'Manual',
    'HELP_OSC_TELECASH_PAYMENT_CAPTURETYPE'                                        => 'Here you specify when the money will be collected for the desired payment method. Depending on the payment method, the following options are possible: 1) DIRECT, 2) AUTOMATICALLY when triggering the shipping notification, 3) MANUALLY in the order admin',

    'OSC_TELECASH_NO_TELECASH_ORDER'       => 'This is no TeleCash-Order',
    'OSC_TELECASH_SUMMARY_ORDER'           => 'Summary TeleCash order',
    'OSC_TELECASH_CHARGE_TOTAL'            => 'Charge Total',
    'OSC_TELECASH_PAYMENT_METHOD'          => 'Payment Method',
    'OSC_TELECASH_STATUS'                  => 'Status',
    'OSC_TELECASH_RESPONSECODE'            => 'Response-Code',
    'OSC_TELECASH_DATE'                    => 'Payment-Date',
    'OSC_TELECASH_TYPE'                    => 'Type',
    'OSC_TELECASH_IPG_TRANSACTION_ID'      => 'IPG Transaction ID',
    'OSC_TELECASH_ENDPOINT_TRANSACTION_ID' => 'Endpoint Transaction ID',
    'OSC_TELECASH_TERMINAL_ID'             => 'Terminal ID',
    'OSC_TELECASH_OCLOCK'                  => 'o\'clock',
    'OSC_TELECASH_HISTORY'                 => 'TeleCash-Order-History',
];