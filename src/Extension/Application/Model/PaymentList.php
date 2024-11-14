<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Extension\Application\Model;

use OxidEsales\Eshop\Application\Model\User;
use OxidSolutionCatalysts\TeleCash\Traits\ModelGetter;
use OxidSolutionCatalysts\TeleCash\Traits\ServiceContainer;

class PaymentList extends PaymentList_parent
{
    use ServiceContainer;
    use ModelGetter;

    /**
     * Constructor for PaymentList.
     *
     * Initializes a new instance of the PaymentList class. This constructor can be used
     * in both production and test environments due to its flexible parameter configuration.
     *
     * @param bool $initParent  Whether to initialize the parent BaseModel.
     *                          Set to false in test environment to avoid
     *                          OXID framework dependencies. Default is true.
     */
    public function __construct(
        bool $initParent = true
    ) {
        if ($initParent) {
            parent::__construct();
        }

        $this->setContainer($this->getContainer());
    }

    /**
     * Core-Extension - var-types and return value only in doc-block
     * {@inheritDoc}
     *
     * @param string $sShipSetId user chosen delivery set
     * @param double $dPrice     basket product price excl. discount
     * @param User   $oUser      session user object
     *
     * @return array<string, \OxidEsales\EshopCommunity\Application\Model\Payment>
     *     Array of payment models indexed by payment id
     */
    public function getPaymentList($sShipSetId, $dPrice, $oUser = null)
    {
        $paymentList = parent::getPaymentList($sShipSetId, $dPrice, $oUser);

        foreach ($paymentList as $oxPaymentId => $paymentListElement) {
            $payment = $this->getOxidPaymentModel();
            /** @var Payment $payment */
            if (
                $payment->load($paymentListElement->getId())
                && !$payment->isTeleCashPaymentValid()
            ) {
                unset($paymentList[$oxPaymentId]);
            }
        }

        return $paymentList;
    }
}
