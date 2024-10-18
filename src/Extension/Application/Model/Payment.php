<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Extension\Application\Model;

use OxidEsales\Eshop\Application\Model\User;

class Payment extends Payment_parent
{
    /**
     * Core-Extension - var-types and return value only in doc-block
     * {@inheritDoc}
     *
     * @param array<string, mixed> $aDynValue dynamical value (in this case oxiddebitnote is checked only)
     * @param string               $sShopId id of current shop
     * @param User                 $oUser the current user
     * @param double               $dBasketPrice the current basket price (oBasket->dPrice)
     * @param string               $sShipSetId the current ship set
     *
     * @return bool true if payment is valid
     */
    public function isValidPayment($aDynValue, $sShopId, $oUser, $dBasketPrice, $sShipSetId)
    {
        $result = parent::isValidPayment($aDynValue, $sShopId, $oUser, $dBasketPrice, $sShipSetId);
        if ($result) {
            $result = $this->isTeleCashPaymentValid();
        }
        return $result;
    }

    /**
     * Checks if the current payment method is a valid TeleCash payment or another payment type.
     *
     * @return bool True if it's a valid TeleCash payment or not a TeleCash payment at all,
     *              false if it's an invalid TeleCash payment.
     */
    private function isTeleCashPaymentValid(): bool
    {
        if ($this->isTeleCashPayment()) {
            return $this->isValidTeleCashPayment();
        }
        return true;
    }

    /**
     * Checks if the current payment method is a TeleCash payment.
     *
     * @return bool True if it's a TeleCash payment, false otherwise.
     */
    private function isTeleCashPayment(): bool
    {
        return false;
    }

    /**
     * Checks if the current TeleCash payment is valid.
     * This method should only be called after confirming that
     * the payment method is indeed a TeleCash payment.
     *
     * @return bool True if the TeleCash payment is valid, false otherwise.
     */
    private function isValidTeleCashPayment(): bool
    {
        return false;
    }
}
