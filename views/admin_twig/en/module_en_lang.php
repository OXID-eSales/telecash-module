<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

use OxidSolutionCatalysts\TeleCash\Core\Module;

$aLang = [
    'charset'                                                                      => 'UTF-8',
    'OSC_TELECASH_PAYMENT_DATA_INITIAL_ERROR'                                      => 'An error occurred during the initial setup of the payment method:',
    'OSC_TELECASH_PAYMENT_IDENT'                                                   => 'Telecash Payment Method',
    'OSC_TELECASH_PAYMENT_IDENT_' . Module::TELECASH_PAYMENT_IDENT_DEFAULT         => 'no TeleCash Payment',
    'OSC_TELECASH_PAYMENT_IDENT_' . Module::TELECASH_PAYMENT_IDENT_CREDITCARD      => 'Credit Card',
    'OSC_TELECASH_PAYMENT_IDENT_' . Module::TELECASH_PAYMENT_IDENT_SOFORT          => 'Instant Bank Transfer',
    'OSC_TELECASH_PAYMENT_IDENT_' . Module::TELECASH_PAYMENT_IDENT_PAYPAL          => 'PayPal',
    'OSC_TELECASH_PAYMENT_IDENT_' . Module::TELECASH_PAYMENT_IDENT_SEPA            => 'SEPA',
    'HELP_OSC_TELECASH_PAYMENT_IDENT'                                              => 'This payment method can be set up as a TeleCash payment method. The options Credit Card, Instant Bank Transfer, PayPal and SEPA Direct Debit are available',
    'OSC_TELECASH_PAYMENT_CAPTURETYPE'                                             => 'Capture Time',
    'OSC_TELECASH_PAYMENT_CAPTURETYPE_' . Module::TELECASH_CAPTURE_TYPE_DIRECT     => 'Direct',
    'OSC_TELECASH_PAYMENT_CAPTURETYPE_' . Module::TELECASH_CAPTURE_TYPE_ONDELIVERY => 'Automatically on Delivery',
    'OSC_TELECASH_PAYMENT_CAPTURETYPE_' . Module::TELECASH_CAPTURE_TYPE_MANUALLY   => 'Manual',
    'HELP_OSC_TELECASH_PAYMENT_CAPTURETYPE'                                        => 'Here you specify when the money will be collected for the desired payment method. Depending on the payment method, the following options are possible: 1) DIRECT, 2) AUTOMATICALLY when triggering the shipping notification, 3) MANUALLY in the order admin',
];