<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\Extension\Application\Controller\Admin;

use OxidSolutionCatalysts\TeleCash\Extension\Application\Controller\Admin\ModuleConfiguration;
use OxidSolutionCatalysts\TeleCash\Core\Service\RegistryService;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleFileSettingsService;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleFileSettingsServiceInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ModuleConfigurationTest extends TestCase
{
    use ProphecyTrait;

    protected $moduleConfiguration;
    protected $registryService;
    protected $fileSettingsService;

    protected function setUp(): void
    {
        $this->moduleConfiguration = $this->prophesize(ModuleConfiguration::class);
        $this->registryService = $this->prophesize(RegistryService::class);
        $this->fileSettingsService = $this->prophesize(ModuleFileSettingsServiceInterface::class);

        //$this->moduleConfiguration->reveal()->registryService = $this->registryService->reveal();
        //$this->moduleConfiguration->reveal()->fileSettingsService = $this->fileSettingsService->reveal();
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
