<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\TeleCash\Exception;

use OxidEsales\Eshop\Core\Exception\StandardException;

class TeleCashException extends StandardException
{
    public function checkIfTeleCashPaymentExistsFail(): self
    {
        return new self('TELECASHPAYMENT_EXISTS_FAIL');
    }
}
