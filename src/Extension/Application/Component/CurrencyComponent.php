<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Extension\Application\Component;

use InvalidArgumentException;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashCurrency;
use OxidSolutionCatalysts\TeleCash\Traits\RequestGetter;
use OxidSolutionCatalysts\TeleCash\Traits\ServiceContainer;

class CurrencyComponent extends CurrencyComponent_parent
{
    use ServiceContainer;
    use RequestGetter;

    /**
     * OXID Core
     *
     * {@inheritDoc}
     *
     * @return null
     */
    public function init()
    {
        $this->setContainer($this->getContainer());

        // TeleCash returns a numeric ISO 4217 currency code in $_POST['currency'].
        // OXID's parent CurrencyComponent::init() reads $_POST['cur'] to set the active shop currency.
        // We must translate and set this before parent::init() runs — there is no OXID API
        // to set the active currency before component initialization, so $_POST is the only option.
        $currency = $this->getStringRequestEscapedData('currency');
        if ($currency) {
            $teleCashCurrency = new TeleCashCurrency();
            try {
                $_POST['cur'] = $teleCashCurrency->getOxidCurrencyIdByCurrencyCode(
                    $currency
                );
            } catch (InvalidArgumentException) {
                // unknown currency code — let OXID use the default
            }
        }

        return parent::init();
    }
}
