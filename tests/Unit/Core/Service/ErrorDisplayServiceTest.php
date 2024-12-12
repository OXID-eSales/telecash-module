<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\Core\Service;

use OxidEsales\Eshop\Core\DisplayError;
use OxidEsales\Eshop\Core\UtilsView;
use PHPUnit\Framework\TestCase;
use OxidSolutionCatalysts\TeleCash\Core\Service\ErrorDisplayService;
use OxidSolutionCatalysts\TeleCash\Core\Service\OxNewService;
use OxidSolutionCatalysts\TeleCash\Core\Service\RegistryService;
use PHPUnit\Framework\MockObject\MockObject;

class ErrorDisplayServiceTest extends TestCase
{
    private ErrorDisplayService $service;
    private MockObject $registryService;
    private MockObject $oxNewService;
    private MockObject $utilsView;
    private MockObject $displayError;

    protected function setUp(): void
    {
        // Create mocks for all dependencies
        $this->registryService = $this->createMock(RegistryService::class);
        $this->oxNewService = $this->createMock(OxNewService::class);
        $this->utilsView = $this->createMock(UtilsView::class);
        $this->displayError = $this->createMock(DisplayError::class);

        // Setup the service with mocked dependencies
        $this->service = new ErrorDisplayService(
            $this->registryService,
            $this->oxNewService
        );
    }

    public function testShowErrorMessageShouldDisplayError(): void
    {
        // Test data
        $errorMessage = 'Test error message';

        // Configure mocks
        $this->oxNewService
            ->expects($this->once())
            ->method('oxNew')
            ->with(DisplayError::class)
            ->willReturn($this->displayError);

        $this->displayError
            ->expects($this->once())
            ->method('setMessage')
            ->with($errorMessage);

        $this->registryService
            ->expects($this->once())
            ->method('getUtilsView')
            ->willReturn($this->utilsView);

        $this->utilsView
            ->expects($this->once())
            ->method('addErrorToDisplay')
            ->with($this->displayError);

        // Execute test
        $this->service->showErrorMessage($errorMessage);
    }
}
