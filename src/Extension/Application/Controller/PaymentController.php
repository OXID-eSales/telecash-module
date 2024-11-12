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


    public function showTeleCashError(): void
    {
        // Wir bekommen von TeleCash Post-Data
        // Damit es ohne weitere Anpassungen in OXID funktioniert, könnten die POST-Data auf die
        // POST oder GET-Variablen übergeben werden. Damit würde OXID sein ganz normales ErrorHandling starten
        //   "payerror"     => "XXX",
        //   "payerrortext" => "YYY",
    }
}
