<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Application\Model\Interface;

use DateTime;

interface TeleCashOrderInterface
{
    /**
     * Set the TeleCash Transaction Result
     * @param array<string, string> $transactionData
     */
    public function setTransactionResult(array $transactionData): void;

    /** get the TxnType */
    public function getTxnType(): string;

    /** get the Oid */
    public function getOid(): string;

    /** get the Currency */
    public function getCurrency(): string;

    /** get the Currency in OXID-Style */
    public function getOxidCurrency(): string;

    /** get Charge Total */
    public function getChargeTotal(): string;

    /** get Charge Total in OXID Style */
    public function getOxidChargeTotal(): float;

    /** get IpgTransactionId */
    public function getIpgTransactionId(): string;

    /** get the Status translated in transaction-language */
    public function getStatus(): string;

    /** get the used Payment Method */
    public function getPaymentMethod(): string;
}
