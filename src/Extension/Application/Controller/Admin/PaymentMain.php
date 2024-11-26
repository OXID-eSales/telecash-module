<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Extension\Application\Controller\Admin;

use Exception;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\TeleCash\Core\Service\RegistryService;
use OxidSolutionCatalysts\TeleCash\Exception\TeleCashException;
use OxidSolutionCatalysts\TeleCash\Application\Model\TeleCashPayment;
use OxidSolutionCatalysts\TeleCash\Core\Module;
use OxidSolutionCatalysts\TeleCash\Core\Service\TranslateServiceInterface;
use OxidSolutionCatalysts\TeleCash\Traits\ModelGetter;
use OxidSolutionCatalysts\TeleCash\Traits\RequestGetter;
use OxidSolutionCatalysts\TeleCash\Traits\ServiceContainer;

class PaymentMain extends PaymentMain_parent
{
    use RequestGetter;
    use ModelGetter;
    use ServiceContainer;

    protected TranslateServiceInterface $translateService;

    protected RegistryService $registryService;

    public function __construct()
    {
        parent::__construct();
        $this->setContainer($this->getContainer());
        $this->translateService = $this->getRequiredService(
            TranslateServiceInterface::class,
            'TranslateServiceInterface'
        );
        $this->registryService = $this->getRequiredService(
            RegistryService::class,
            'RegistryService'
        );
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

    /**
     * @throws TeleCashException
     */
    private function addTeleCashToTemplate(): void
    {
        $teleCashPayment = $this->getTeleCashPayment();

        // these variables are needed in any case
        $this->addTplParam('teleCashModuleId', Module::MODULE_ID);
        $this->addTplParam('teleCashCaptureTypeDirect', Module::TELECASH_CAPTURE_TYPE_DIRECT);
        $this->addTplParam('teleCashCaptureTypeOnDelivery', Module::TELECASH_CAPTURE_TYPE_ONDELIVERY);
        $this->addTplParam('teleCashCaptureTypeManually', Module::TELECASH_CAPTURE_TYPE_MANUALLY);
        $this->addTplParam('teleCashIdentDBField', Module::TELECASH_DB_FIELD_IDENT);
        $this->addTplParam('teleCashCaptureTypeDbField', Module::TELECASH_DB_FIELD_CAPTURETYPE);

        if ($teleCashPayment) {
            $teleCashIdentValue = $teleCashPayment->getTeleCashIdent();

            // this variables are needed, if we have a valid teleCashPayment
            $this->addTplParam('isTeleCashPayment', true);
            $this->addTplParam(
                'teleCashIdents',
                $teleCashPayment->getPossibleTeleCashIdents()
            );
            $this->addTplParam(
                'teleCashIdentValue',
                $teleCashIdentValue
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

    /**
     * @throws TeleCashException
     */
    private function getTeleCashPayment(): ?TeleCashPayment
    {
        $result = null;

        $oxid = $this->getEditObjectId();
        $teleCashPayment = $this->getTeleCashPaymentModel();

        if ($teleCashPayment->loadByPaymentId($oxid)) {
            $result = $teleCashPayment;
        }

        return $result;
    }

    /**
     * create TeleCashPayment Datas
     * @throws TeleCashException
     */
    private function createTeleCashPayment(): bool
    {
        $teleCashPayment = $this->getTeleCashPaymentModel();
        try {
            $teleCashPayment->setPaymentId($this->getEditObjectId());
            $teleCashPayment->setTeleCashIdent();
            $teleCashPayment->setTeleCashCaptureType();
            $result = (bool) $teleCashPayment->save();
        } catch (Exception $e) {
            $this->registryService->getUtilsView()->addErrorToDisplay(
                $this->translateService->translateString('OSC_TELECASH_PAYMENT_DATA_INITIAL_ERROR') . $e->getMessage()
            );
            return false;
        }
        return $result;
    }

    /**
     * save TeleCashPayment if exists
     * @throws TeleCashException
     */
    private function saveTeleCashPayment(): bool
    {
        $result = true;

        $params = $this->getArrayRequestEscapedData('editval');

        $ident = $params[Module::TELECASH_DB_FIELD_IDENT] ?? '';
        $captureType = $params[Module::TELECASH_DB_FIELD_CAPTURETYPE] ?? '';

        $teleCashPayment = $this->getTeleCashPayment();

        if ($teleCashPayment && $ident && $captureType) {
            $teleCashPayment->setPaymentId($this->getEditObjectId());
            $teleCashPayment->setTeleCashIdent($ident);
            $teleCashPayment->setTeleCashCaptureType($captureType);
            try {
                $result = (bool) $teleCashPayment->save();
            } catch (TeleCashException | Exception $e) {
                $this->registryService->getUtilsView()->addErrorToDisplay(
                    $e->getMessage()
                );
            }
        }

        return $result;
    }
}
