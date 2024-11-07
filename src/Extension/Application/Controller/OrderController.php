<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Extension\Application\Controller;

use OxidEsales\Eshop\Application\Model\Address;
use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Core\Exception\LanguageNotFoundException;
use OxidSolutionCatalysts\TeleCash\Application\Model\TeleCashPayment;
use OxidSolutionCatalysts\TeleCash\Core\Module;
use OxidSolutionCatalysts\TeleCash\Core\Service\Context;
use OxidSolutionCatalysts\TeleCash\Core\Service\RegistryService;
use OxidSolutionCatalysts\TeleCash\Exception\TeleCashException;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleSettingsService;
use OxidSolutionCatalysts\TeleCash\Traits\ModelGetter;
use OxidSolutionCatalysts\TeleCash\Traits\RequestGetter;
use OxidSolutionCatalysts\TeleCash\Traits\ServiceContainer;

class OrderController extends OrderController_parent
{
    use ServiceContainer;
    use ModelGetter;
    use RequestGetter;

    protected ?TeleCashPayment $teleCashPayment = null;

    public function __construct()
    {
        parent::__construct();

        $this->setContainer($this->getContainer());
    }

    /**
     * OXID-Core
     * {@inheritDoc}
     *
     * @return string
     * @throws TeleCashException
     * @throws LanguageNotFoundException
     */
    public function render()
    {
        $result = parent::render();

        $this->addTeleCashToTemplate();

        return $result;
    }

    /**
     * @throws TeleCashException
     * @throws LanguageNotFoundException
     */
    private function addTeleCashToTemplate(): void
    {
        $teleCashPayment = $this->getTeleCashPayment();
        $registryService = $this->getServiceFromContainer(RegistryService::class);
        $context = $this->getServiceFromContainer(Context::class);
        $moduleSettings = $this->getServiceFromContainer(ModuleSettingsService::class);

        // these variables are needed in any case
        $this->addTplParam('teleCashModuleId', Module::MODULE_ID);
        $this->addTplParam('isTeleCashPayment', (bool) $teleCashPayment);

        if ($teleCashPayment) {
            $basket = $this->getBasket();

            $teleCashConnectData = $this->getTeleCashConnectData();

            // Language
            $oxidLanguage = $registryService ? $registryService->getLang()->getLanguageAbbr() : '';
            $language = strtoupper($oxidLanguage);

            // possible DeliveryAddress
            $deliveryId = $this->getStringRequestEscapedData("deladrid");
            if ($deliveryId) {
                $address = $this->getOxNewService()->oxNew(Address::class);
                $address->load($deliveryId);
                $teleCashConnectData->setOxidAddress($address);
            }

            //Urls
            $failUrl = $context ? $context->getFailUrl() : '';
            $successUrl = $context ? $context->getSuccessUrl($basket) : '';
            $notificationUrl = $context ? $context->getNotificationUrl() : '';
            $connectUrl = $moduleSettings ? $moduleSettings->getConnectUrl() : '';

            $teleCashConnectData->setOxidBasket($basket);
            $teleCashConnectData->setOxidLanguage($language);
            $teleCashConnectData->setFailUrl($failUrl);
            $teleCashConnectData->setSuccessUrl($successUrl);
            $teleCashConnectData->setNotificationUrl($notificationUrl);
            $teleCashConnectData->setTransactionType($teleCashPayment->getTeleCashTransactionType());
            $teleCashConnectData->setPaymentMethod($teleCashPayment->getTeleCashPaymentMethod());

            $this->addTplParam(
                'teleCashHiddenData',
                $teleCashConnectData->getTeleCashConnectDataAsHiddenFields()
            );
            $this->addTplParam(
                'teleCashConnectUrl',
                $connectUrl
            );
        }
    }

    /**
     * @throws TeleCashException
     */
    private function getTeleCashPayment(): ?TeleCashPayment
    {
        if (is_null($this->teleCashPayment)) {
            /** @var Payment $payment */
            $payment = $this->getPayment();
            if ($payment) {
                $oxid = $payment->getId();
                $teleCashPayment = $this->getTeleCashPaymentModel();
                if ($teleCashPayment->loadByPaymentId($oxid)) {
                    $this->teleCashPayment = $teleCashPayment;
                }
            }
        }

        return $this->teleCashPayment;
    }
}
