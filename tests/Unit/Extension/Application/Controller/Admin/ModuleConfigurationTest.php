<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\Extension\Application\Controller\Admin;

use OxidSolutionCatalysts\TeleCash\Extension\Application\Controller\Admin\ModuleConfiguration;
use OxidSolutionCatalysts\TeleCash\Core\Service\RegistryService;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleFileSettingsServiceInterface;
use PHPUnit\Framework\TestCase;

class ModuleConfigurationTest extends TestCase
{
    protected $moduleConfiguration;
    protected $registryService;
    protected $fileSettingsService;

    protected function setUp(): void
    {
        $this->moduleConfiguration = $this->createPartialMock(ModuleConfiguration::class, []);
        $this->registryService = $this->createMock(RegistryService::class);
        $this->fileSettingsService = $this->createMock(ModuleFileSettingsServiceInterface::class);

        // If you need to set the properties, you can do so like this:
        // $this->moduleConfiguration->registryService = $this->registryService;
        // $this->moduleConfiguration->fileSettingsService = $this->fileSettingsService;
    }

    public function testRender()
    {
    }

    public function testSaveConfVars()
    {
    }

    public function testStoreTeleCashFiles()
    {
    }

    public function testDeleteTeleCashFiles()
    {
    }
}
