<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\Core\Service;

use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Language;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\UtilsView;
use OxidSolutionCatalysts\TeleCash\Core\Service\RegistryService;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

#[CoversClass(RegistryService::class)]
class RegistryServiceTest extends TestCase
{
    use ProphecyTrait;

    private RegistryService $registryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->registryService = new RegistryService();
    }

    public function testGetConfig(): void
    {
        $mockConfig = $this->prophesize(Config::class);
        Registry::set(Config::class, $mockConfig->reveal());

        $result = $this->registryService->getConfig();

        $this->assertInstanceOf(Config::class, $result);
        $this->assertSame($mockConfig->reveal(), $result);
    }

    public function testGetLang(): void
    {
        $mockLang = $this->prophesize(Language::class);
        Registry::set(Language::class, $mockLang->reveal());

        $result = $this->registryService->getLang();

        $this->assertInstanceOf(Language::class, $result);
        $this->assertSame($mockLang->reveal(), $result);
    }

    public function testGetRequest(): void
    {
        $mockRequest = $this->prophesize(Request::class);
        Registry::set(Request::class, $mockRequest->reveal());

        $result = $this->registryService->getRequest();

        $this->assertInstanceOf(Request::class, $result);
        $this->assertSame($mockRequest->reveal(), $result);
    }

    public function testGetUtilsView(): void
    {
        $mockUtilsView = $this->prophesize(UtilsView::class);
        Registry::set(UtilsView::class, $mockUtilsView->reveal());

        $result = $this->registryService->getUtilsView();

        $this->assertInstanceOf(UtilsView::class, $result);
        $this->assertSame($mockUtilsView->reveal(), $result);
    }
}
