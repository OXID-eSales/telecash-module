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

    /**
     * @var TranslateServiceInterface|null $translateService
     */
    protected ?TranslateServiceInterface $translateService;

    public function __construct(
        bool $initParent = true
    ) {
        if ($initParent) {
            parent::__construct();
        }

        $this->setContainer($this->getContainer());
    }

    /**
     * Collect TeleCash-Error and transfer to OXID payerrortext
     * @throws TeleCashException
     */
    public function provideTeleCashError(): void
    {
        $telecashConnect = $this->getTeleCashConnect();
        $telecashConnect->setResponseData($_POST);

        $defaultError = $this->getTranslationService()->translateString('TELECASH_DEFAULT_PAYMENT_ERROR');
        $teleCashError = '';

        if ($telecashConnect->isValidResponse()) {
            $transactionResult = $telecashConnect->getTransactionResult();
            $teleCashError = $transactionResult['fail_reason'] ?: '';
        }

        $this->_sPaymentErrorText = $teleCashError ?: $defaultError;
        $this->_sPaymentError = '-1';
    }

    protected function getTranslationService(): TranslateServiceInterface
    {
        if ($this->translateService === null) {
            $this->translateService = $this->getRequiredService(
                TranslateServiceInterface::class,
                'TranslateServiceInterface'
            );
        }
        return $this->translateService;
    }
}
