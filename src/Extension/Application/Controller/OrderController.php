<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Extension\Application\Controller;

use OxidEsales\Eshop\Application\Model\Payment;
use OxidSolutionCatalysts\TeleCash\Application\Model\TeleCashPayment;
use OxidSolutionCatalysts\TeleCash\Core\Module;
use OxidSolutionCatalysts\TeleCash\Traits\ModelGetter;
use OxidSolutionCatalysts\TeleCash\Traits\ServiceContainer;

class OrderController extends OrderController_parent
{
    use ServiceContainer;
    use ModelGetter;

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
     */
    public function render()
    {
        $result = parent::render();

        $this->addTeleCashToTemplate();

        return $result;
    }

    private function addTeleCashToTemplate(): void
    {
        $teleCashPayment = $this->getTeleCashPayment();

        // these variables are needed in any case
        $this->addTplParam('teleCashModuleId', Module::MODULE_ID);
        $this->addTplParam('isTeleCashPayment', (bool) $teleCashPayment);

        if ($teleCashPayment) {
            $this->addTplParam(
                'teleCashPaymentMethod',
                $teleCashPayment->getTeleCashPaymentMethod()
            );
            $this->addTplParam(
                'teleCashTransactionType',
                'sale'
            );
        }
    }

    private function getTeleCashPayment(): ?TeleCashPayment
    {
        if (is_null($this->teleCashPayment)) {
            /** @var Payment $payment */
            $payment = $this->getPayment();
            if ($payment) {
                $oxid = $payment->getId();
                $teleCashPayment = $this->getTeleCashPaymentModel();
                if (
                    $teleCashPayment
                    && $teleCashPayment->loadByPaymentId($oxid)
                ) {
                    $this->teleCashPayment = $teleCashPayment;
                }
            }
        }

        return $this->teleCashPayment;
    }
}
