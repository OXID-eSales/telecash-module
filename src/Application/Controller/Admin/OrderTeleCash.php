<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Application\Controller\Admin;

use Exception;
use OxidEsales\Eshop\Application\Controller\Admin\AdminController;
use OxidEsales\Eshop\Application\Model\Order as oxOrder;
use OxidSolutionCatalysts\TeleCash\Application\Model\TeleCashOrder;
use OxidSolutionCatalysts\TeleCash\Application\Model\TeleCashPayment;
use OxidSolutionCatalysts\TeleCash\Core\Module;
use OxidSolutionCatalysts\TeleCash\Core\Service\ErrorDisplayServiceInterface;
use OxidSolutionCatalysts\TeleCash\Core\Service\RegistryService;
use OxidSolutionCatalysts\TeleCash\Core\Service\TeleCashAfterProcessServiceInterface;
use OxidSolutionCatalysts\TeleCash\Exception\TeleCashException;
use OxidSolutionCatalysts\TeleCash\Extension\Application\Model\Order;
use OxidSolutionCatalysts\TeleCash\Extension\Application\Model\Payment;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleSettingsServiceInterface;
use OxidSolutionCatalysts\TeleCash\Traits\ModelGetter;
use OxidSolutionCatalysts\TeleCash\Traits\RequestGetter;
use OxidSolutionCatalysts\TeleCash\Traits\ServiceContainer;

class OrderTeleCash extends AdminController
{
    use ModelGetter;
    use RequestGetter;
    use ServiceContainer;

    protected $_sThisTemplate = '@' . Module::MODULE_ID . '/admin/order_telecash';

    protected RegistryService $registryService;
    protected TeleCashAfterProcessServiceInterface $afterProcessService;
    private ErrorDisplayServiceInterface $errorDisplay;

    protected ?string $oxid = null;
    protected bool $isTeleCashOrder = false;
    protected ?TeleCashOrder $teleCashOrder = null;
    protected ?TeleCashPayment $teleCashPayment = null;
    protected oxOrder $order;

    /**
     * Constructor for Order.
     *
     * Initializes a new instance of the OrderTeleCash class. This constructor can be used
     * in both production and test environments due to its flexible parameter configuration.
     *
     * @param bool $initParent Whether to initialize the parent BaseModel.
     *                          Set to false in test environment to avoid
     *                          OXID framework dependencies. Default is true.
     * @throws TeleCashException
     */
    public function __construct(
        bool $initParent = true
    ) {
        if ($initParent) {
            parent::__construct();
        }

        $this->setContainer($this->getContainer());
        $this->registryService = $this->getRequiredService(
            RegistryService::class,
            'RegistryService'
        );

        $this->afterProcessService = $this->getRequiredService(
            TeleCashAfterProcessServiceInterface::class,
            'TeleCashAfterProcessService'
        );

        $this->errorDisplay = $this->getRequiredService(
            ErrorDisplayServiceInterface::class,
            'ErrorDisplayService'
        );

        $this->initPayment();
    }

    /** @inheritdoc
     * @throws TeleCashException
     */
    public function render()
    {
        parent::render();

        $this->addTeleCashToTemplate();

        return $this->getTemplateName();
    }

    /**
     * do Charge triggered in Template as Controller fnc=doChargeOrder
     * @throws Exception
     */
    public function doChargeOrder(): void
    {
        $amount = $this->getFloatRequestData('chargeAmount');
        if (
            !is_null($this->teleCashOrder) &&
            $this->isChargePossible($amount)
        ) {
            $this->afterProcessService->doChargeOrder($this->teleCashOrder->getId(), $amount);

            // reload the order
            $this->initPayment();
        }
    }

    /**
     * @throws TeleCashException
     */
    private function addTeleCashToTemplate(): void
    {
        // these variables are needed in any case
        $this->addTplParam('oxid', $this->oxid);
        $this->addTplParam('teleCashModuleId', Module::MODULE_ID);

        if ($this->isTeleCashOrder && !is_null($this->teleCashOrder)) {
            $this->addTeleCashOrderToTemplate();
            return;
        }

        $this->errorDisplay->showErrorMessage('OSC_TELECASH_NO_TELECASH_ORDER');
    }

    /**
     * @throws TeleCashException
     */
    private function addTeleCashOrderToTemplate(): void
    {
        // bulletproof
        if (is_null($this->teleCashOrder)) {
            return;
        }

        $isNettoMode = $this->order->isNettoMode();
        $chargeTotalFloat = $this->teleCashOrder->getOxidChargeTotal();
        $currencyString = $this->teleCashOrder->getOxidCurrency();
        $chargeTotalObject = $this->getPriceObj($chargeTotalFloat, $isNettoMode);
        $currencyObject = $this->registryService->getConfig()->getCurrencyObject($currencyString);
        $txnType = $this->teleCashOrder->getTxnType();
        $chargeAmountFloat = $this->getPossibleCharge();

        // History
        $teleCashOrderHistoryList = $this->teleCashOrder->getTeleCashOrderHistoryList();

        $this->addTplParam('isTeleCashOrder', true);
        $this->addTplParam('isChargePossible', $this->isChargePossible());
        $this->addTplParam('chargeTotalObject', $chargeTotalObject);
        $this->addTplParam('chargeAmountFloat', $chargeAmountFloat);
        $this->addTplParam('currencyString', $currencyString);
        $this->addTplParam('currencyObject', $currencyObject);
        $this->addTplParam('paymentMethod', $this->teleCashOrder->getPaymentMethod());
        $this->addTplParam('status', $this->teleCashOrder->getStatus());
        $this->addTplParam('txnType', $txnType);
        $this->addTplParam('oId', $this->teleCashOrder->getOid());
        $this->addTplParam('teleCashOrderHistoryList', $teleCashOrderHistoryList);

        if ($this->teleCashPayment) {
            $this->addTplParam('captureType', $this->teleCashPayment->getTeleCashCaptureType());
        }
    }

    /**
     * @throws TeleCashException
     */
    private function isChargePossible(?float $amount = null): bool
    {
        $amount = !is_null($amount) ? $amount : $this->getPossibleCharge();

        /** @var ModuleSettingsServiceInterface $moduleSettings */
        $moduleSettings = $this->getServiceFromContainer(ModuleSettingsServiceInterface::class);

        return
            $moduleSettings->isValidBackendConfiguration() &&
            !is_null($this->teleCashOrder) &&
            $this->teleCashOrder->getTxnType() !== Module::TELECASH_TXN_TYPE_SALE &&
            $amount > 0 &&
            $amount <= $this->getPossibleCharge();
    }

    /**
     * @throws TeleCashException
     */
    private function getPossibleCharge(): float
    {
        $result = 0.0;
        if (!is_null($this->teleCashOrder)) {
            $result = $this->teleCashOrder->getPossibleCharge();
        }
        return $result;
    }

    private function initPayment(): void
    {
        // initial oxID
        $this->oxid = $this->getEditObjectId();

        // initial possible TeleCashOrder
        $this->order = $this->getOxidOrderModel();
        $this->order->load($this->oxid);

        /** @var Order $order */
        $order = $this->order;

        $this->isTeleCashOrder = $order->isTeleCashOrder();
        $this->teleCashOrder = $order->getTeleCashOrder();

        //initial possible TeleCashPayment
        $payment = $this->getOxidPaymentModel();
        $paymentId = $order->getFieldStringData('oxpaymenttype');

        $payment->load($paymentId);
        /** @var Payment $payment */
        $this->teleCashPayment = $payment->getTeleCashPayment();
    }
}
