<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\Core\Service;

use OxidSolutionCatalysts\TeleCash\Core\Service\OxNewService;
use PHPUnit\Framework\TestCase;

#[CoversClass(OxNewService::class)]
class OxNewServiceTest extends TestCase
{
    private OxNewService $oxNewService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->oxNewService = new OxNewService();
    }

    public function testOxNewWithoutOxNewFunction(): void
    {
        $result = $this->oxNewService->oxNew(\stdClass::class);
        $this->assertInstanceOf(\stdClass::class, $result);
    }

    public function testOxNewWithConstructorArgs(): void
    {
        $testClass = new class (0, '') {
            public function __construct(
                public int $id,
                public string $name
            ) {
            }
        };

        $result = $this->oxNewService->oxNew(get_class($testClass), [1, 'Test']);
        $this->assertInstanceOf(get_class($testClass), $result);
        $this->assertEquals(1, $result->id);
        $this->assertEquals('Test', $result->name);
    }

    public function testOxNewWithOxNewFunction(): void
    {
        if (!function_exists('oxNew')) {
            function oxNew(string $className, ...$args)
            {
                return new $className(...$args);
            }
        }

        $result = $this->oxNewService->oxNew(\stdClass::class);
        $this->assertInstanceOf(\stdClass::class, $result);
    }
}
