<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Tests\Integration\Core\Service;

/**
 * Class DummyClassWithConstructor
 *
 * This is a dummy class with a constructor, used for testing the OxNewService.
 * It is specifically designed to be used in OxNewServiceIntegrationTest to verify
 * that constructor arguments are correctly passed.
 *
 * @see OxNewServiceTest
 */
class DummyClassWithConstructor
{
    private $arg;

    /**
     * @param mixed $arg An argument to be stored and retrieved later
     */
    public function __construct($arg)
    {
        $this->arg = $arg;
    }

    /**
     * @return mixed The argument passed to the constructor
     */
    public function getArg()
    {
        return $this->arg;
    }
}
