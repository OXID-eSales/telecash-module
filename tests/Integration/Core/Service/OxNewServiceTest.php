<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Tests\Integration\Core\Service;

use OxidSolutionCatalysts\TeleCash\Core\Service\OxNewService;
use PHPUnit\Framework\TestCase;

/**
 * Class OxNewServiceIntegrationTest
 *
 * This class contains integration tests for the OxNewService.
 * It uses two dummy classes for testing purposes:
 * - DummyClass: A simple class without a constructor
 * - DummyClassWithConstructor: A class with a constructor that accepts an argument
 *
 * @see DummyClass
 * @see DummyClassWithConstructor
 */
final class OxNewServiceTest extends TestCase
{
    private OxNewService $oxNewService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->oxNewService = new OxNewService();
    }

    /**
     * Test creating an instance of a simple class without constructor
     */
    public function testOxNewWithSimpleClass(): void
    {
        $object = $this->oxNewService->oxNew(DummyClass::class);
        $this->assertInstanceOf(DummyClass::class, $object);
    }

    /**
     * Test creating an instance of a class with constructor arguments
     */
    public function testOxNewWithConstructorArguments(): void
    {
        $arg = 'test';
        $object = $this->oxNewService->oxNew(DummyClassWithConstructor::class, [$arg]);
        $this->assertInstanceOf(DummyClassWithConstructor::class, $object);
        $this->assertEquals($arg, $object->getArg());
    }
}
