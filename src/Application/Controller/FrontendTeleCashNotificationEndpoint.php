<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Application\Controller;

use OxidEsales\Eshop\Core\Controller\BaseController;
use OxidSolutionCatalysts\TeleCash\Exception\TeleCashException;
use OxidSolutionCatalysts\TeleCash\Traits\ModelGetter;
use OxidSolutionCatalysts\TeleCash\Traits\RequestGetter;
use OxidSolutionCatalysts\TeleCash\Traits\ServiceContainer;

/**
 * Class FrontendTeleCashNotificationEndpoint
 *
 * Controller for handling TeleCash-specific Notifications
 * This controller provides functionality for retrieving various TeleCash-notifications
 * through POST responses.
 *
 * @package OxidSolutionCatalysts\TeleCash\Application\Controller
 */
class FrontendTeleCashNotificationEndpoint extends BaseController
{
    use ModelGetter;
    use RequestGetter;
    use ServiceContainer;

    /**
     * Constructor for the FrontendTeleCashNotificationEndpoint controller
     *
     * Initializes the controller with required dependencies and optionally
     * calls the parent constructor.
     *
     * @param bool $initParent Whether to initialize the parent controller (default: true)
     */
    public function __construct(bool $initParent = true)
    {
        if ($initParent) {
            parent::__construct();
        }
        $this->setContainer($this->getContainer());
    }

    /**
     * Collect TeleCash-Notification and add this to order
     *
     * @throws TeleCashException
     * TODO remove PHPMD.UnusedLocalVariable
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function receiveNotifications(): void
    {
        $telecashConnect = $this->getTeleCashConnect();
        $telecashConnect->setResponseData($_POST);
        if (!$telecashConnect->isValidResponse()) {
            /** Throw an Error is too hard, but Ok for the moment, Show an Error is better */
            throw (new TeleCashException())->noValidTransactionResult();
        }

        /** TODO follow up the work ...
         * We´ve got TeleCashPost-Data
         * The notification result contains new informations for the order.
         */
        $notificationResult = $telecashConnect->getTransactionResult();
    }
}
