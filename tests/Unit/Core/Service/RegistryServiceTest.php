<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\Core\Service;

use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Language;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\UtilsView;
use OxidSolutionCatalysts\TeleCash\Core\Service\RegistryService;
use PHPUnit\Framework\TestCase;

#[CoversClass(RegistryService::class)]
class RegistryServiceTest extends TestCase
{
    private RegistryService $registryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->registryService = new RegistryService();
    }

    public function testGetConfig(): void
    {
        $mockConfig = $this->createMock(Config::class);
        Registry::set(Config::class, $mockConfig);

        $result = $this->registryService->getConfig();

        $this->assertInstanceOf(Config::class, $result);
        $this->assertSame($mockConfig, $result);
    }

    public function testGetLang(): void
    {
        $mockLang = $this->createMock(Language::class);
        Registry::set(Language::class, $mockLang);

        $result = $this->registryService->getLang();

        $this->assertInstanceOf(Language::class, $result);
        $this->assertSame($mockLang, $result);
    }

    public function testGetRequest(): void
    {
        $mockRequest = $this->createMock(Request::class);
        Registry::set(Request::class, $mockRequest);

        $result = $this->registryService->getRequest();

        $this->assertInstanceOf(Request::class, $result);
        $this->assertSame($mockRequest, $result);
    }

    public function testGetUtilsView(): void
    {
        $mockUtilsView = $this->createMock(UtilsView::class);
        Registry::set(UtilsView::class, $mockUtilsView);

        $result = $this->registryService->getUtilsView();

        $this->assertInstanceOf(UtilsView::class, $result);
        $this->assertSame($mockUtilsView, $result);
    }
}
