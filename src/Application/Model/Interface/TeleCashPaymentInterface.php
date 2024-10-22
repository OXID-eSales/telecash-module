<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Application\Model\Interface;

interface TeleCashPaymentInterface
{
    /**
     * Sets the payment ID
     */
    public function setPaymentId(string $paymentId): void;

    /**
     * Returns the payment ID
     */
    public function getPaymentId(): string;

    /**
     * Returns an array of possible TeleCash identifiers
     * @return array<string>
     */
    public function getPossibleTeleCashIdents(): array;

    /**
     * Returns the current TeleCash identifier
     */
    public function getTeleCashIdent(): string;

    /**
     * Sets the TeleCash identifier
     */
    public function setTeleCashIdent(string $ident = ''): void;

    /**
     * Validates the TeleCash identifier
     */
    public function validTeleCashIdent(string $ident = ''): string;

    /**
     * Returns an array of possible capture types for a given identifier
     * @return array<string>
     */
    public function getPossibleTeleCashCaptureTypes(string $ident = ''): array;

    /**
     * Returns the current capture type
     */
    public function getTeleCashCaptureType(): string;

    /**
     * Sets the capture type
     */
    public function setTeleCashCaptureType(string $captureType = ''): void;

    /**
     * Validates the capture type
     */
    public function validTeleCashCaptureType(string $captureType = '', string $ident = ''): string;

    /**
     * Loads TeleCash-Payment by using paymentid instead of oxid
     */
    public function loadByPaymentId(string $paymentId = ''): bool;
}
