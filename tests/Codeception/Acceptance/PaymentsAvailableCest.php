<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Tests\Codeception\Acceptance;

use OxidSolutionCatalysts\TeleCash\Tests\Codeception\Support\AcceptanceTester;

final class PaymentsAvailableCest
{
    /**
     * @group CreditCardPayment
     */
    public function checkPayments(AcceptanceTester $I): void
    {
        $I->amGoingTo("Check if Telecash payments is available");
        $I->openShop();
        $I->assertEqual(0, 1);
    }
}
