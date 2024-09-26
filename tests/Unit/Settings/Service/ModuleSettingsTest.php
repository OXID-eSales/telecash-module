<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Tests\Settings\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingService;
use OxidSolutionCatalysts\TeleCash\Core\Module;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleSettingsService;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleSettingsServiceInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\String\UnicodeString;

#[CoversClass(ModuleSettingsService::class)]
final class ModuleSettingsTest extends TestCase
{
    /**
     * @dataProvider getApiModeDataProvider
     * @throws Exception
     */
    public function testgetApiMode(string $value, string $expected): void
    {
        $mssMock = $this->createPartialMock(ModuleSettingService::class, ['getString']);
        $mssMock->method('getString')->willReturnMap([
            [ModuleSettingsServiceInterface::API_MODE, Module::MODULE_ID, new UnicodeString($value)]
        ]);

        $sut = new ModuleSettingsService($mssMock);
        $this->assertSame($expected, $sut->getApiMode());
    }

    public static function getApiModeDataProvider(): array
    {
        return [
            [
                'value' => '',
                'expected' => ModuleSettingsServiceInterface::API_MODE_LIVE
            ],
            [
                'value' => 'someUnpredictable',
                'expected' => ModuleSettingsServiceInterface::API_MODE_LIVE
            ],
            [
                'value' => ModuleSettingsServiceInterface::API_MODE_LIVE,
                'expected' => ModuleSettingsServiceInterface::API_MODE_LIVE
            ],
            [
                'value' => ModuleSettingsServiceInterface::API_MODE_SANDBOX,
                'expected' => ModuleSettingsServiceInterface::API_MODE_SANDBOX
            ],
        ];
    }

    /**
     * @dataProvider isLiveApiModeDataProvider
     * @throws Exception
     */
    public function testisLiveApiMode(string $value, bool $expected): void
    {
        $mssMock = $this->createPartialMock(ModuleSettingService::class, ['getString']);
        $mssMock->method('getString')->willReturnMap([
            [ModuleSettingsServiceInterface::API_MODE, Module::MODULE_ID, new UnicodeString($value)]
        ]);

        $sut = new ModuleSettingsService($mssMock);
        $this->assertSame($expected, $sut->isLiveApiMode());
    }

    public static function isLiveApiModeDataProvider(): array
    {
        return [
            [
                'value' => ModuleSettingsServiceInterface::API_MODE_LIVE,
                'expected' => false
            ],
            [
                'value' => ModuleSettingsServiceInterface::API_MODE_SANDBOX,
                'expected' => true
            ],
        ];
    }

    /**
     * @throws Exception
     */
    public function testsaveApiMode(): void
    {
        $value = 'someValue';

        $mssMock = $this->createPartialMock(ModuleSettingService::class, ['saveString']);
        $mssMock->expects($this->atLeastOnce())->method('saveString')->with(
            ModuleSettingsServiceInterface::API_MODE,
            $value,
            Module::MODULE_ID
        );

        $sut = new ModuleSettingsService($mssMock);
        $sut->saveApiMode($value);
    }
}
