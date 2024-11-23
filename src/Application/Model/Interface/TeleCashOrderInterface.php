<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Application\Model\Interface;

interface TeleCashOrderInterface
{
    /**
     * Set the TeleCash Transaction Result
     * @param array<string, string> $transactionData
     */
    public function setTransactionResult(array $transactionData): void;

    /** get the TxnType */
    public function getTxnType(): string;

    /** get the Txn DateTime */
    public function getTxnDateTime(): string;

    /** get the Oid */
    public function getOid(): string;

    /** get the EndpointTransactionId */
    public function getEndpointTransactionId(): string;

    /** get the Terminal ID */
    public function getTerminalId(): string;

    /** get the IPG Transaction ID */
    public function getIpgTransactionId(): string;

    /** get the Currency */
    public function getCurrency(): string;

    /** get the EndpointTransactionId */
    public function getChargeTotal(): float;

    /** get the Status translated in transaction-language */
    public function getStatus(): string;

    /** get the Status as Code, named in TeleCash as ProcessorResponseCode */
    public function getProcessorResponseCode(): string;

    /** get the used Payment Method */
    public function getPaymentMethod(): string;
}
