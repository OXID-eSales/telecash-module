<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Extension\Application\Model;

use OxidSolutionCatalysts\TeleCash\Application\Model\TeleCashOrder;
use OxidSolutionCatalysts\TeleCash\Application\Model\TeleCashOrderHistory;
use OxidSolutionCatalysts\TeleCash\Exception\TeleCashException;
use OxidSolutionCatalysts\TeleCash\Traits\DataGetter;
use OxidSolutionCatalysts\TeleCash\Traits\ModelGetter;
use OxidSolutionCatalysts\TeleCash\Traits\ServiceContainer;

class Order extends Order_parent
{
    use DataGetter;
    use ModelGetter;
    use ServiceContainer;

    protected ?TeleCashOrder $teleCashOrder = null;

    protected ?TeleCashOrderHistory $teleCashOrderHistory = null;

    protected ?bool $teleCashOrderIsLoaded = null;

    /**
     * Constructor for Order.
     *
     * Initializes a new instance of the Order class. This constructor can be used
     * in both production and test environments due to its flexible parameter configuration.
     *
     * @param bool $initParent  Whether to initialize the parent BaseModel.
     *                          Set to false in test environment to avoid
     *                          OXID framework dependencies. Default is true.
     */
    public function __construct(
        bool $initParent = true
    ) {
        if ($initParent) {
            parent::__construct();
        }

        $this->setContainer($this->getContainer());
    }

    /**
     * Checks if the current order is a TeleCash order.
     *
     * @return bool True if it's a TeleCash order, false otherwise.
     * @throws TeleCashException
     */
    public function isTeleCashOrder(): bool
    {
        return (bool) $this->getTeleCashOrder();
    }

    /**
     * Load the teleCash-Order. For performance, it is only loaded once a time
     *
     * @throws TeleCashException
     */
    public function getTeleCashOrder(): ?TeleCashOrder
    {
        if (is_null($this->teleCashOrderIsLoaded)) {
            $this->teleCashOrderIsLoaded = false;
            $orderId = $this->getId();
            $teleCashOrder = $this->getTeleCashOrderModel();
            $teleCashOrder->loadByOrderId($orderId);
            $this->teleCashOrderIsLoaded = $teleCashOrder->isLoaded();
            $this->teleCashOrder = $teleCashOrder;
        }
        return $this->teleCashOrder;
    }
}
