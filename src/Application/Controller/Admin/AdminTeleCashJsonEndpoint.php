<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Application\Controller\Admin;

use OxidEsales\Eshop\Core\Controller\BaseController;
use OxidSolutionCatalysts\TeleCash\Core\Module;
use OxidSolutionCatalysts\TeleCash\Core\Service\RegistryService;
use OxidSolutionCatalysts\TeleCash\Traits\ControllerGetter;
use OxidSolutionCatalysts\TeleCash\Traits\Json;
use OxidSolutionCatalysts\TeleCash\Traits\RequestGetter;
use OxidSolutionCatalysts\TeleCash\Traits\ServiceContainer;

/**
 * Class AdminTeleCashJsonEndpoint
 *
 * Controller for handling TeleCash-specific JSON endpoints in the admin area.
 * This controller provides functionality for retrieving various TeleCash-related
 * configuration and status information through JSON responses.
 *
 * Key features:
 * - Retrieval of possible TeleCash capture types
 * - JSON response handling
 * - Request parameter processing
 *
 * Used traits:
 * - ControllerGetter: Provides access to TeleCash payment models
 * - Json: Handles JSON encoding/decoding and response output
 * - RequestGetter: Processes and validates request parameters
 *
 * @package OxidSolutionCatalysts\TeleCash\Application\Controller\Admin
 */
class AdminTeleCashJsonEndpoint extends BaseController
{
    use ControllerGetter;
    use Json;
    use RequestGetter;
    use ServiceContainer;

    /**
     * Constructor for the AdminTeleCashJsonEndpoint controller
     *
     * Initializes the controller with required dependencies and optionally
     * calls the parent constructor.
     *
     * @param RegistryService $registryService The registry service for accessing shop components
     * @param bool $initParent Whether to initialize the parent controller (default: true)
     */
    public function __construct(
        private readonly RegistryService $registryService,
        bool $initParent = true
    ) {
        if ($initParent) {
            parent::__construct();
        }
    }

    /**
     * Retrieves possible TeleCash capture types and outputs them as JSON
     *
     * This method:
     * 1. Attempts to get the TeleCash payment model
     * 2. If successful, retrieves the TeleCash identifier from the request
     * 3. Gets possible capture types for the given identifier
     * 4. Returns the result as a JSON response
     *
     * Response format:
     * - Success: JSON array of available capture types
     * - Failure: Empty JSON array
     *
     * Example success response:
     * ["direct", "manually", "ondelivery"]
     *
     * @return void Response is output directly as JSON
     */
    public function getPossibleTeleCashCaptureTypes(): void
    {
        $resultArr = [];

        $teleCashPayment = $this->getTeleCashPaymentModel();
        if ($teleCashPayment) {
            $valueKey = Module::TELECASH_DB_FIELD_IDENT;
            $teleCashIdentValue = $this->getStringRequestEscapedData($valueKey);
            $resultArr = $teleCashPayment->getPossibleTeleCashCaptureTypes($teleCashIdentValue);
        }

        $result = $this->arrayToJson($resultArr);
        $this->outputJson($result);
    }
}
