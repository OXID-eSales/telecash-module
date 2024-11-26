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

    public function serviceNotFound(): self
    {
        return new self('TELECASH_SERVICE_MISSING');
    }

    public function noValidTransactionResult(): self
    {
        return new self('TELECASH_NO_VALID_TRANSACTION_RESULT');
    }

    public function isNotTeleCash(): self
    {
        return new self('TELECASH_PAYMENT_ISNT_TELECASH');
    }
}
