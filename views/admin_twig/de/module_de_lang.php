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

    // Admin PaymentMain
    'OSC_TELECASH_PAYMENT_DATA_INITIAL_ERROR'                                      => 'Beim initialen Einrichten der Zahlart ist ein Fehler aufgetreten:',
    'OSC_TELECASH_PAYMENT_IDENT'                                                   => 'Telecash Zahlart',
    'OSC_TELECASH_PAYMENT_IDENT_' . Module::TELECASH_PAYMENT_IDENT_DEFAULT         => 'keine TeleCash Zahlart',
    'OSC_TELECASH_PAYMENT_IDENT_' . Module::TELECASH_PAYMENT_IDENT_TELECASH        => 'TeleCash Connect',
    'OSC_TELECASH_PAYMENT_IDENT_' . Module::TELECASH_PAYMENT_IDENT_CC_AMERICAN     => 'Kreditkarte American Express',
    'OSC_TELECASH_PAYMENT_IDENT_' . Module::TELECASH_PAYMENT_IDENT_CC_MASTERCARD   => 'Kreditkarte Mastercard',
    'OSC_TELECASH_PAYMENT_IDENT_' . Module::TELECASH_PAYMENT_IDENT_CC_VISA         => 'Kreditkarte Visa',
    'OSC_TELECASH_PAYMENT_IDENT_' . Module::TELECASH_PAYMENT_IDENT_PAYPAL          => 'PayPal',
    'OSC_TELECASH_PAYMENT_IDENT_' . Module::TELECASH_PAYMENT_IDENT_SEPA            => 'SEPA',
    'OSC_TELECASH_TXNTYPE_' . Module::TELECASH_TXN_TYPE_SALE                       => 'Einzug',
    'OSC_TELECASH_TXNTYPE_' . Module::TELECASH_TXN_TYPE_POSTAUTH                   => 'Autorisierung',
    'HELP_OSC_TELECASH_PAYMENT_IDENT'                                              => 'Diese Zahlart kann als TeleCash-Zahlart angelegt werden. Es stehen die Optionen Kreditkarte, Sofort-Überweisung, PayPal und SEPA-LAstschrift zur Verfügung',
    'OSC_TELECASH_PAYMENT_CAPTURETYPE'                                             => 'TeleCash Einzugszeitpunkt',
    'OSC_TELECASH_PAYMENT_CAPTURETYPE_' . Module::TELECASH_CAPTURE_TYPE_DIRECT     => 'Direkt',
    'OSC_TELECASH_PAYMENT_CAPTURETYPE_' . Module::TELECASH_CAPTURE_TYPE_ONDELIVERY => 'Automatisch bei Lieferung',
    'OSC_TELECASH_PAYMENT_CAPTURETYPE_' . Module::TELECASH_CAPTURE_TYPE_MANUALLY   => 'Manuell',
    'HELP_OSC_TELECASH_PAYMENT_CAPTURETYPE'                                        => 'Hiermit legen Sie fest, wann das Geld für die gewünschte Zahlart eingezogen wird. Je nach Zahlart ist möglich: 1) DIREKT, 2) AUTOMATISCH beim auslösen der Versandbenachrichtigung, 3) MANUELL im Bestelladmin',

    // Admin OrderTeleCash
    'OSC_TELECASH_NO_TELECASH_ORDER'       => 'Das ist keine TeleCash-Bestellung',
    'OSC_TELECASH_SUMMARY_ORDER'           => 'Zusammenfassung TeleCash-Bestellung',
    'OSC_TELECASH_CHARGE_TOTAL'            => 'Gesamtsumme',
    'OSC_TELECASH_PAYMENT_METHOD'          => 'Zahlmethode',
    'OSC_TELECASH_STATUS'                  => 'Status',
    'OSC_TELECASH_RESPONSECODE'            => 'Antwort-Code',
    'OSC_TELECASH_DATE'                    => 'Zahl-Datum',
    'OSC_TELECASH_TYPE'                    => 'Typ',
    'OSC_TELECASH_IPG_TRANSACTION_ID'      => 'IPG Transaction ID',
    'OSC_TELECASH_ENDPOINT_TRANSACTION_ID' => 'Endpoint Transaction ID',
    'OSC_TELECASH_TERMINAL_ID'             => 'Terminal ID',
    'OSC_TELECASH_OCLOCK'                  => 'Uhr',
];