<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Extension\Application\Controller;

use OxidSolutionCatalysts\TeleCash\Core\Service\TranslateServiceInterface;
use OxidSolutionCatalysts\TeleCash\Exception\TeleCashException;
use OxidSolutionCatalysts\TeleCash\Traits\ModelGetter;
use OxidSolutionCatalysts\TeleCash\Traits\RequestGetter;
use OxidSolutionCatalysts\TeleCash\Traits\ServiceContainer;

class PaymentController extends PaymentController_parent
{
    use ServiceContainer;
    use ModelGetter;
    use RequestGetter;

    protected TranslateServiceInterface $translateService;

    public function __construct()
    {
        parent::__construct();

        $this->setContainer($this->getContainer());
        $this->translateService = $this->getRequiredService(
            TranslateServiceInterface::class,
            'TranslateServiceInterface'
        );
    }

    /**
     * Collect TeleCash-Error and transfer to OXID payerrortext
     * @throws TeleCashException
     */
    public function provideTeleCashError(): void
    {
        $telecashConnect = $this->getTeleCashConnect();
        $telecashConnect->setResponseData($_POST);

        $defaultError = $this->translateService->translateString('TELECASH_DEFAULT_PAYMENT_ERROR');
        $teleCashError = '';

        if ($telecashConnect->isValidResponse()) {
            $transactionResult = $telecashConnect->getTransactionResult();
            $teleCashError = $transactionResult['fail_reason'] ?: '';
        }

        $this->_sPaymentErrorText = $teleCashError ?: $defaultError;
        $this->_sPaymentError = '-1';
    }
}
