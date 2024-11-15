<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Extension\Application\Controller;

use OxidEsales\Eshop\Application\Model\Address;
use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Core\Exception\LanguageNotFoundException;
use OxidSolutionCatalysts\TeleCash\Application\Model\TeleCashPayment;
use OxidSolutionCatalysts\TeleCash\Core\Module;
use OxidSolutionCatalysts\TeleCash\Core\Service\Context;
use OxidSolutionCatalysts\TeleCash\Core\Service\RegistryService;
use OxidSolutionCatalysts\TeleCash\Exception\TeleCashException;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleSettingsServiceInterface;
use OxidSolutionCatalysts\TeleCash\Traits\ModelGetter;
use OxidSolutionCatalysts\TeleCash\Traits\RequestGetter;
use OxidSolutionCatalysts\TeleCash\Traits\ServiceContainer;

class PaymentController extends PaymentController_parent
{
    use ServiceContainer;
    use ModelGetter;
    use RequestGetter;

    public function __construct()
    {
        parent::__construct();

        $this->setContainer($this->getContainer());
    }


    /**
     * Collect TeleCash-Error and transfer to OXID payerrortext and payerror
     *
     * @throws TeleCashException
     * TODO remove PHPMD.UnusedLocalVariable
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function showTeleCashError(): void
    {
        $telecashConnect = $this->getTeleCashConnect();
        $telecashConnect->addPostData($_POST);

        /** TODO follow up the work ...
         * We´ve got TeleCashPost-Data
         * The transaction result contains error codes and error texts that we should map to the OXID variables.
         * OXID can then display the error message according to the OXID standard.
         * "payerror"     => "XXX",
         * "payerrortext" => "YYY",
        */
        $transactionResult = $telecashConnect->getTransactionResult();
    }
}
