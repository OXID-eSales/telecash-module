<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Extension\Application\Controller\Admin;

use Exception;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\TeleCash\Exception\TeleCashException;
use OxidSolutionCatalysts\TeleCash\Application\Model\TeleCashPayment;
use OxidSolutionCatalysts\TeleCash\Core\Module;
use OxidSolutionCatalysts\TeleCash\Core\Service\OxNewService;
use OxidSolutionCatalysts\TeleCash\Core\Service\TranslateServiceInterface;
use OxidSolutionCatalysts\TeleCash\Traits\RequestGetter;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class PaymentMain extends PaymentMain_parent
{
    use RequestGetter;

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
     * {@inheritDoc}
     *
     * @return string
     */
    public function render()
    {
        $result = parent::render();

        $this->addTeleCashToTemplate();

        return $result;
    }

    /**
     * OXID-Core
     * @inheritDoc
     * @return void
     * @throws Exception
     */
    public function save()
    {
        // check whether creation of TeleCash is necessary
        $createTeleCashPayment =
            $this->getEditObjectId() === '-1'
            && !$this->getTeleCashPayment();

        parent::save();

        if ($createTeleCashPayment) {
            $this->createTeleCashPayment();
            return;
        }

        $this->saveTeleCashPayment();
    }

    private function addTeleCashToTemplate(): void
    {
        $teleCashPayment = $this->getTeleCashPayment();
        if ($teleCashPayment) {
            $teleCashIdentValue = $teleCashPayment->getTeleCashIdent();
            $this->addTplParam(
                'isTeleCashPayment',
                true
            );
            $this->addTplParam(
                'teleCashIdentDBField',
                Module::TELECASH_DB_FIELD_IDENT
            );
            $this->addTplParam(
                'teleCashIdents',
                $teleCashPayment->getPossibleTeleCashIdents()
            );
            $this->addTplParam(
                'teleCashIdentValue',
                $teleCashIdentValue
            );
            $this->addTplParam(
                'teleCashCaptureTypeDbField',
                Module::TELECASH_DB_FIELD_CAPTURETYPE
            );
            $this->addTplParam(
                'teleCashCaptureTypes',
                $teleCashPayment->getPossibleTeleCashCaptureTypes($teleCashIdentValue)
            );
            $this->addTplParam(
                'teleCashCaptureTypeValue',
                $teleCashPayment->getTeleCashCaptureType()
            );
        }
    }

    private function getTeleCashPayment(): ?TeleCashPayment
    {
        $result = null;

        $oxid = $this->getEditObjectId();
        if (
            $this->getTeleCashPaymentModel()
            && $this->getTeleCashPaymentModel()->loadByPaymentId($oxid)
        ) {
            $result = $this->getTeleCashPaymentModel();
        }
        return $result;
    }

    /**
     * create TeleCashPayment Datas
     */
    private function createTeleCashPayment(): bool
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

    /**
     * save TeleCashPayment if exists
     */
    private function saveTeleCashPayment(): bool
    {
        $result = true;

        $params = $this->getArrayRequestEscapedData('editval');

        $ident = $params[Module::TELECASH_DB_FIELD_IDENT] ?? '';
        $captureType = $params[Module::TELECASH_DB_FIELD_CAPTURETYPE] ?? '';

        $teleCashPayment = $this->getTeleCashPayment();

        if ($teleCashPayment && $ident && $captureType) {
            $teleCashPayment->setTeleCashIdent($ident);
            $teleCashPayment->setTeleCashCaptureType($captureType);
            try {
                $result = (bool) $teleCashPayment->save();
            } catch (TeleCashException | Exception $e) {
                Registry::getUtilsView()->addErrorToDisplay(
                    $e->getMessage()
                );
            }
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
