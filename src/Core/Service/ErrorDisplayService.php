<?php

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Core\Service;

use OxidEsales\Eshop\Core\DisplayError;

/**
 * Service for displaying error messages in the OXID admin interface.
 * Provides a centralized way to show error messages across the application.
 */
class ErrorDisplayService implements ErrorDisplayServiceInterface
{
    private RegistryService $registryService;
    private OxNewService $oxNewService;

    public function __construct(
        RegistryService $registryService,
        OxNewService $oxNewService
    ) {
        $this->registryService = $registryService;
        $this->oxNewService = $oxNewService;
    }

    /**
     * Displays an error message in the OXID admin interface.
     * Creates a new DisplayError object and adds it to the view stack.
     *
     * @param string $errorMessage The error message to be displayed
     * @return void
     */
    public function showErrorMessage(string $errorMessage): void
    {
        $displayError = $this->oxNewService->oxNew(DisplayError::class);
        $displayError->setMessage($errorMessage);
        $this->registryService->getUtilsView()->addErrorToDisplay($displayError);
    }
}
