<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Application\Model\Interface;

use OxidEsales\Eshop\Application\Model\Address;
use OxidEsales\Eshop\Application\Model\Basket;

interface TeleCashConnectDataInterface
{
    /**
     * Sets the OXID-User
     */
    public function setOxidBasket(Basket $basket): void;

    /**
     * Sets additional OXID-User-Delivery-Address
     */
    public function setOxidAddress(Address $address): void;

    /**
     * Sets OXID-Language e.g. EN
     */
    public function setOxidLanguage(string $language): void;

    /**
     * Sets TeleCash TransactionType e.g. sale
     */
    public function setTransactionType(string $txnType): void;

    /**
     * Sets TeleCash paymentMethod e.g. 'M'
     */
    public function setPaymentMethod(string $paymentMethod): void;

    /**
     * Sets TeleCash Fail Url
     */
    public function setFailUrl(string $responseFailURL): void;

    /**
     * Sets TeleCash Success Url
     */
    public function setSuccessUrl(string $responseSuccessURL): void;

    /**
     * Sets TeleCash Notification Url
     */
    public function setNotificationUrl(string $transactionNotificationURL): void;

    /**
     * get TeleCash formed Data
     *
     * @return array<string, string>
     */
    public function getTeleCashConnectData(): array;
}
