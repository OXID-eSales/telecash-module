<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Extension\Application\Controller\Admin;

use Exception;
use OxidSolutionCatalysts\TeleCash\Application\Model\TeleCashPayment;
use OxidSolutionCatalysts\TeleCash\Traits\ServiceContainer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class PaymentMain extends PaymentMain_parent
{
    use ServiceContainer;

    protected TeleCashPayment $teleCashPayment;

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __construct()
    {
        parent::__construct();
        $this->teleCashPayment = $this->getServiceFromContainer(TeleCashPayment::class);
    }

    /**
     * OXID-Core
     * @inheritDoc
     * @return void
     * @throws Exception
     */
    public function save()
    {
        parent::save();

        if (!$this->isTeleCashPaymentDataExists()) {
            $this->createTeleCashPaymentData();
        }
    }

    /**
     * check if TeleCashPayment still exists
     */
    private function isTeleCashPaymentDataExists(): bool
    {
        $oxid = $this->getEditObjectId();
        return $this->teleCashPayment->loadByPaymentId($oxid);
    }

    /**
     * create TeleCashPayment Datas
     * @throws Exception
     */
    private function createTeleCashPaymentData(): void
    {
        $params = [
            'oxpaymentid' => $this->getEditObjectId()
        ];
        $this->teleCashPayment->assign($params);
        $this->teleCashPayment->save();
    }
}
