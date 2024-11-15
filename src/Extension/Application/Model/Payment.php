<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Extension\Application\Model;

use OxidEsales\Eshop\Application\Model\User;
use OxidSolutionCatalysts\TeleCash\Application\Model\TeleCashPayment;
use OxidSolutionCatalysts\TeleCash\Core\Module;
use OxidSolutionCatalysts\TeleCash\Exception\TeleCashException;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleSettingsServiceInterface;
use OxidSolutionCatalysts\TeleCash\Traits\ModelGetter;
use OxidSolutionCatalysts\TeleCash\Traits\ServiceContainer;

class Payment extends Payment_parent
{
    use ModelGetter;
    use ServiceContainer;

    protected ModuleSettingsServiceInterface $moduleSettings;

    /**
     * Constructor for Payment.
     *
     * Initializes a new instance of the Payment class. This constructor can be used
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
        $this->moduleSettings = $this->getRequiredService(
            ModuleSettingsServiceInterface::class,
            'ModuleSettingsServiceInterface'
        );
    }

    protected ?TeleCashPayment $teleCashPayment = null;

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
     * @throws TeleCashException
     */
    public function isTeleCashPaymentValid(): bool
    {
        if ($this->isTeleCashPayment()) {
            return $this->isValidTeleCashConfiguration();
        }
        return true;
    }

    /**
     * Checks if the current payment method is a TeleCash payment.
     *
     * @return bool True if it's a TeleCash payment, false otherwise.
     * @throws TeleCashException
     */
    public function isTeleCashPayment(): bool
    {
        $this->teleCashPayment = $this->getTeleCashPaymentModel();
        $this->teleCashPayment->loadByPaymentId($this->getId());
        return $this->teleCashPayment->getTeleCashIdent() !== Module::TELECASH_PAYMENT_IDENT_DEFAULT;
    }

    /**
     * Checks if the current TeleCash paymentconfiguration is valid.
     * This method should only be called after confirming that
     * the payment method is indeed a TeleCash payment.
     *
     * @return bool True if the TeleCash payment is valid, false otherwise.
     */
    protected function isValidTeleCashConfiguration(): bool
    {
        return $this->isAdmin() ?
            $this->moduleSettings->isValidBackendConfiguration() :
            $this->moduleSettings->isValidFrontendConfiguration();
    }
}
