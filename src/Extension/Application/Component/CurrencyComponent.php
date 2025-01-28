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

        // translate the passed currency parameter from Telecash,
        // since the same parameter is also used by OXID.
        $currency = $this->getStringRequestEscapedData('currency');
        if ($currency) {
            $teleCashCurrency = new TeleCashCurrency();
            try {
                $_POST['cur'] = $teleCashCurrency->getOxidCurrencyIdByCurrencyCode(
                    $currency
                );
            } catch (InvalidArgumentException) {
                // do nothing
            }
        }

        return parent::init();
    }
}
