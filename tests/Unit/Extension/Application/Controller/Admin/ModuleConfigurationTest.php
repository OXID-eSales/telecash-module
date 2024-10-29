<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\Extension\Application\Controller\Admin;

use OxidSolutionCatalysts\TeleCash\Extension\Application\Controller\Admin\ModuleConfiguration;
use OxidSolutionCatalysts\TeleCash\Core\Service\RegistryService;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleFileSettingsService;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleFileSettingsServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ModuleConfigurationTest extends TestCase
{
    protected $moduleConfiguration;
    protected $registryService;
    protected $fileSettingsService;

    protected function setUp(): void
    {
        $this->moduleConfiguration = $this->createMock(ModuleConfiguration::class);
        $this->registryService = $this->createMock(RegistryService::class);
        $this->fileSettingsService = $this->createMock(ModuleFileSettingsServiceInterface::class);
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
