<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Extension\Application\Controller;

use OxidEsales\Eshop\Application\Model\Address;
use OxidSolutionCatalysts\TeleCash\Core\Module;
use OxidSolutionCatalysts\TeleCash\Core\Service\Context;
use OxidSolutionCatalysts\TeleCash\Exception\TeleCashException;
use OxidSolutionCatalysts\TeleCash\Extension\Application\Model\Payment;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleLanguageSettingsServiceInterface;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleSettingsServiceInterface;
use OxidSolutionCatalysts\TeleCash\Traits\ModelGetter;
use OxidSolutionCatalysts\TeleCash\Traits\RequestGetter;
use OxidSolutionCatalysts\TeleCash\Traits\ServiceContainer;

class OrderController extends OrderController_parent
{
    use ServiceContainer;
    use ModelGetter;
    use RequestGetter;

    public function __construct(
        bool $initParent = true
    ) {
        if ($initParent) {
            parent::__construct();
        }

        $this->setContainer($this->getContainer());
    }

    /**
     * OXID-Core
     * {@inheritDoc}
     *
     * @return string
     * @throws TeleCashException
     */
    public function render()
    {
        $result = parent::render();

        $this->addTeleCashToTemplate();

        return $result;
    }

    /**
     * @throws TeleCashException
     */
    private function addTeleCashToTemplate(): void
    {
        /** @var Payment $payment */
        $payment = $this->getPayment();
        $isTeleCashPayment = $payment->isTeleCashPayment();

        // these variables are needed in any case
        $this->addTplParam('teleCashModuleId', Module::MODULE_ID);
        $this->addTplParam('isTeleCashPayment', $isTeleCashPayment);

        if ($isTeleCashPayment) {
            $this->addTplParam(
                'teleCashHiddenData',
                $this->collectHiddenData($payment)
            );
            $this->addTplParam(
                'teleCashConnectUrl',
                $this->collectConnectUrl()
            );
        }
    }

    /**
     * collect all necessary Datas as a huge hidden field
     *
     * @throws TeleCashException
     */
    private function collectHiddenData(Payment $payment): string
    {
        $context = $this->getServiceFromContainer(Context::class);
        $languageSettings = $this->getServiceFromContainer(ModuleLanguageSettingsServiceInterface::class);

        $teleCashPayment = $payment->getTeleCashPayment();

        if (!$teleCashPayment) {
            return '';
        }

        $basket = $this->getBasket();

        $teleCashConnectData = $this->getTeleCashConnectData();

        // Language
        $language = $languageSettings ?
            $languageSettings->getLocaleForCountryIso() :
            ModuleLanguageSettingsServiceInterface::DEFAULT_LOCALE;

        // possible DeliveryAddress
        $deliveryId = $this->getStringRequestEscapedData("deladrid");
        if ($deliveryId) {
            $address = $this->getOxNewService()->oxNew(Address::class);
            if ($address->load($deliveryId)) {
                $teleCashConnectData->setOxidAddress($address);
            }
        }

        //Urls
        $failUrl = $context ? $context->getFailUrl() : '';
        $successUrl = $context ? $context->getSuccessUrl($basket, $this->getDeliveryAddressMD5()) : '';

        $transactionType = $teleCashPayment->getTeleCashTransactionType();
        $paymentMethod = $teleCashPayment->getTeleCashPaymentMethod();

        $teleCashConnectData->setOxidBasket($basket);
        $teleCashConnectData->setOxidLanguage($language);
        $teleCashConnectData->setFailUrl($failUrl);
        $teleCashConnectData->setSuccessUrl($successUrl);
        $teleCashConnectData->setTransactionType($transactionType);
        $teleCashConnectData->setPaymentMethod($paymentMethod);

        return $teleCashConnectData->getTeleCashConnectDataAsHiddenFields();
    }

    /**
     * collect the Connect-Url als Form-Post-Parameter
     */
    private function collectConnectUrl(): string
    {
        $moduleSettings = $this->getServiceFromContainer(ModuleSettingsServiceInterface::class);
        return $moduleSettings ? $moduleSettings->getConnectUrl() : '';
    }
}
