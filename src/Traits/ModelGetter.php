<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Traits;

use OxidEsales\Eshop\Application\Model\Payment;
use OxidSolutionCatalysts\TeleCash\Application\Model\TeleCashOrder;
use OxidSolutionCatalysts\TeleCash\Application\Model\TeleCashPayment;
use OxidSolutionCatalysts\TeleCash\Core\Service\OxNewService;
use OxidSolutionCatalysts\TeleCash\Core\Service\TeleCashPaymentValidatorServiceInterface;
use OxidSolutionCatalysts\TeleCash\Exception\TeleCashException;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashConnect;
use OxidSolutionCatalysts\TeleCash\Application\Model\TeleCashConnectData;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleSettingsServiceInterface;

/**
 * Convenience trait to work with Controller-Models.
 */
trait ModelGetter
{
    /**
     * @throws TeleCashException
     */
    private function getOxNewService(): OxNewService
    {
        $oxNewService = $this->getServiceFromContainer(OxNewService::class);
        if (!$oxNewService) {
            throw (new TeleCashException())->serviceNotFound();
        }
        return $oxNewService;
    }

    /**
     * @throws TeleCashException
     */
    private function getTeleCashPaymentModel(): TeleCashPayment
    {
        $validator = $this->getRequiredService(
            TeleCashPaymentValidatorServiceInterface::class,
            'TeleCashPaymentValidatorService'
        );
        return $this->getOxNewService()->oxNew(TeleCashPayment::class, [$validator]);
    }

    /**
     * @throws TeleCashException
     */
    private function getTeleCashOrderModel(string $oxOrderId): TeleCashOrder
    {
        return $this->getOxNewService()->oxNew(TeleCashOrder::class, [$oxOrderId]);
    }

    /**
     * @throws TeleCashException
     */
    private function getOxidPaymentModel(): Payment
    {
        return $this->getOxNewService()->oxNew(Payment::class);
    }



    /**
     * @throws TeleCashException
     */
    private function getTeleCashConnect(): TeleCashConnect
    {
        $moduleSettings = $this->getServiceFromContainer(ModuleSettingsServiceInterface::class);
        $storeId = $moduleSettings ? $moduleSettings->getStoreId() : '';
        $password = $moduleSettings ? $moduleSettings->getSharedSecret() : '';
        return $this->getOxNewService()->oxNew(
            TeleCashConnect::class,
            [
                $storeId,
                $password
            ]
        );
    }

    /**
     * @throws TeleCashException
     */
    private function getTeleCashConnectData(): TeleCashConnectData
    {
        return $this->getOxNewService()->oxNew(
            TeleCashConnectData::class,
            [
                $this->getTeleCashConnect(),
                $this->getOxNewService()
            ]
        );
    }
}
