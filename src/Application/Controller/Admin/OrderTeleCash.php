<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Application\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminController;
use OxidSolutionCatalysts\TeleCash\Application\Model\TeleCashOrder;
use OxidSolutionCatalysts\TeleCash\Application\Model\TeleCashPayment;
use OxidSolutionCatalysts\TeleCash\Core\Module;
use OxidSolutionCatalysts\TeleCash\Core\Service\RegistryService;
use OxidSolutionCatalysts\TeleCash\Exception\TeleCashException;
use OxidSolutionCatalysts\TeleCash\Extension\Application\Model\Order;
use OxidSolutionCatalysts\TeleCash\Extension\Application\Model\Payment;
use OxidSolutionCatalysts\TeleCash\Traits\ModelGetter;
use OxidSolutionCatalysts\TeleCash\Traits\ServiceContainer;

class OrderTeleCash extends AdminController
{
    use ModelGetter;
    use ServiceContainer;

    protected $_sThisTemplate = '@' . Module::MODULE_ID . '/admin/order_telecash';

    protected RegistryService $registryService;

    /**
     * Constructor for Order.
     *
     * Initializes a new instance of the OrderTeleCash class. This constructor can be used
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
        $this->registryService = $this->getRequiredService(
            RegistryService::class,
            'RegistryService'
        );
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
     * @throws TeleCashException
     */
    private function addTeleCashToTemplate(): void
    {
        $oxId = $this->getEditObjectId();

        /** @var Order $order */
        $order = $this->getOxidOrderModel();
        $order->load($oxId);
        $isNettoMode = $order->isNettoMode();

        $isTeleCashOrder = $order->isTeleCashOrder();
        $teleCashOrder = $order->getTeleCashOrder();

        $paymentId = $order->getFieldStringData('oxpaymenttype');
        $payment = $this->getOxidPaymentModel();

        $payment->load($paymentId);
        /** @var Payment $payment */
        $teleCashPayment = $payment->getTeleCashPayment();

        // these variables are needed in any case
        $this->addTplParam('oxid', $oxId);
        $this->addTplParam('teleCashModuleId', Module::MODULE_ID);
        $this->addTplParam('isTeleCashOrder', $isTeleCashOrder);

        if ($isTeleCashOrder && $teleCashOrder) {
            $this->addTeleCashOrderToTemplate(
                $teleCashOrder,
                $isNettoMode,
                $teleCashPayment
            );
        }
    }

    /**
     * @throws TeleCashException
     */
    private function addTeleCashOrderToTemplate(
        TeleCashOrder $teleCashOrder,
        bool $isNettoMode,
        ?TeleCashPayment $teleCashPayment
    ): void {

        $chargeTotal = $teleCashOrder->getChargeTotal();
        $currency = $teleCashOrder->getCurrency();
        $priceChargeTotal = $this->getPriceObj($chargeTotal, $isNettoMode);
        $currencyObj = $this->registryService->getConfig()->getCurrencyObject($currency);

        // History
        $teleCashOrderHistoryList = $teleCashOrder->getTeleCashOrderHistoryList();

        $this->addTplParam('priceChargeTotal', $priceChargeTotal);
        $this->addTplParam('currencyObj', $currencyObj);
        $this->addTplParam('paymentMethod', $teleCashOrder->getPaymentMethod());
        $this->addTplParam('status', $teleCashOrder->getStatus());
        $this->addTplParam('txnType', $teleCashOrder->getTxnType());
        $this->addTplParam('teleCashOrderHistoryList', $teleCashOrderHistoryList);

        if ($teleCashPayment) {
            $this->addTplParam('captureType', $teleCashPayment->getTeleCashCaptureType());
        }
    }
}
