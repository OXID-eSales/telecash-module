<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Extension\Application\Controller\Admin;

use Exception;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\TeleCash\Application\Model\TeleCashPayment;
use OxidSolutionCatalysts\TeleCash\Core\Service\OxNewService;
use OxidSolutionCatalysts\TeleCash\Core\Service\TranslateServiceInterface;
use OxidSolutionCatalysts\TeleCash\Traits\ServiceContainer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class PaymentMain extends PaymentMain_parent
{
    use ServiceContainer;

    protected TranslateServiceInterface $translateService;

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __construct()
    {
        parent::__construct();
        $this->translateService = $this->getServiceFromContainer(TranslateServiceInterface::class);
    }

    /**
     * OXID-Core
     * @inheritDoc
     * @return void
     * @throws Exception
     */
    public function save()
    {
        $createTeleCashPaymentData =
            $this->getEditObjectId() === '-1'
            && !$this->isTeleCashPaymentDataExists();

        parent::save();

        if ($createTeleCashPaymentData) {
            $this->createTeleCashPaymentData();
        }
    }

    /**
     * check if TeleCashPayment still exists
     */
    private function isTeleCashPaymentDataExists(): bool
    {
        $oxid = $this->getEditObjectId();
        return (bool) $this->getTeleCashPaymentModel()?->loadByPaymentId($oxid);
    }

    /**
     * create TeleCashPayment Datas
     */
    private function createTeleCashPaymentData(): bool
    {
        $result = false;
        $teleCashPayment = $this->getTeleCashPaymentModel();
        try {
            if ($teleCashPayment) {
                $teleCashPayment->setPaymentId($this->getEditObjectId());
                $teleCashPayment->setTeleCashIdent();
                $teleCashPayment->setTeleCashCaptureType();
                $result = (bool) $teleCashPayment->save();
            }
        } catch (Exception $e) {
            Registry::getUtilsView()->addErrorToDisplay(
                $this->translateService->translateString('OSC_TELECASH_PAYMENT_DATA_INITIAL_ERROR') . $e->getMessage()
            );
            return false;
        }
        return $result;
    }

    private function getTeleCashPaymentModel(): ?TeleCashPayment
    {
        try {
            return $this
                ->getServiceFromContainer(OxNewService::class)
                ->oxNew(TeleCashPayment::class);
        } catch (NotFoundExceptionInterface | ContainerExceptionInterface) {
            return null;
        }
    }
}
