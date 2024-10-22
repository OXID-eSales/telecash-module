<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

use OxidSolutionCatalysts\TeleCash\Core\Module;

$aLang = [
    'charset'                                                                      => 'UTF-8',
    'OSC_TELECASH_PAYMENT_DATA_INITIAL_ERROR'                                      => 'Beim initialen Einrichten der Zahlart ist ein Fehler aufgetreten:',
    'OSC_TELECASH_PAYMENT_IDENT'                                                   => 'Telecash Zahlart',
    'OSC_TELECASH_PAYMENT_IDENT_' . Module::TELECASH_PAYMENT_IDENT_DEFAULT         => 'keine TeleCash Zahlart',
    'OSC_TELECASH_PAYMENT_IDENT_' . Module::TELECASH_PAYMENT_IDENT_CREDITCARD      => 'Kreditkarte',
    'OSC_TELECASH_PAYMENT_IDENT_' . Module::TELECASH_PAYMENT_IDENT_SOFORT          => 'Sofort-Überweisung',
    'OSC_TELECASH_PAYMENT_IDENT_' . Module::TELECASH_PAYMENT_IDENT_PAYPAL          => 'PayPal',
    'OSC_TELECASH_PAYMENT_IDENT_' . Module::TELECASH_PAYMENT_IDENT_SEPA            => 'SEPA',
    'HELP_OSC_TELECASH_PAYMENT_IDENT'                                              => 'Diese Zahlart kann als TeleCash-Zahlart angelegt werden. Es stehen die Optionen Kreditkarte, Sofort-Überweisung, PayPal und SEPA-LAstschrift zur Verfügung',
    'OSC_TELECASH_PAYMENT_CAPTURETYPE'                                             => 'Einzugszeitpunkt',
    'OSC_TELECASH_PAYMENT_CAPTURETYPE_' . Module::TELECASH_CAPTURE_TYPE_DIRECT     => 'Direkt',
    'OSC_TELECASH_PAYMENT_CAPTURETYPE_' . Module::TELECASH_CAPTURE_TYPE_ONDELIVERY => 'Automatisch bei Lieferung',
    'OSC_TELECASH_PAYMENT_CAPTURETYPE_' . Module::TELECASH_CAPTURE_TYPE_MANUALLY   => 'Manuell',
    'HELP_OSC_TELECASH_PAYMENT_CAPTURETYPE'                                        => 'Hiermit legen Sie fest, wann das Geld für die gewünschte Zahlart eingezogen wird. Je nach Zahlart ist möglich: 1) DIREKT, 2) AUTOMATISCH beim auslösen der Versandbenachrichtigung, 3) MANUELL im Bestelladmin',
];