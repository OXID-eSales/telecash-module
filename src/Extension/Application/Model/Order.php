<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Extension\Application\Model;

use Doctrine\DBAL\Exception;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidSolutionCatalysts\TeleCash\Application\Model\TeleCashOrder;
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
        return $this->getTeleCashOrder() !== null;
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
            if ($this->teleCashOrderIsLoaded) {
                $this->teleCashOrder = $teleCashOrder;
            }
        }
        return $this->teleCashOrder;
    }

    /**
     * Core-Extension - var-types and return value only in doc-block
     * {@inheritDoc}
     *
     * @param string $oxid Object ID(default null)
     *
     * @return bool
     * @throws TeleCashException
     */
    public function delete($oxid = null)
    {
        $oxid = $oxid ?: $this->getId();

        if ($oxid && $this->isTeleCashOrder()) {
            $teleCashOrder = $this->getTeleCashOrder();
            $teleCashOrder?->delete();
        }

        return parent::delete($oxid);
    }

    /**
     * Mark order as paid
     *
     * @return void
     */
    public function teleCashMarkAsPaid(): void
    {
        $date = date('Y-m-d H:i:s');
        $this->updateDBField('oxpaid', $date);
    }

    /**
     * set TeleCash Trans ID
     *
     * @param string $transId
     * @return void
     */
    public function teleCashSetTransId(string $transId): void
    {
        $this->updateDBField('oxtransid', $transId);
    }

    /**
     * set a value to the oxorder database
     *
     * @param string $field
     * @param string $value
     * @return void
     */
    private function updateDBField(string $field, string $value): void
    {
        $queryBuilderFactory = $this->getServiceFromContainer(QueryBuilderFactoryInterface::class);
        if (!$queryBuilderFactory) {
            return;
        }

        $queryBuilder = $queryBuilderFactory->create();
        $oxId = $this->getId();
        try {
            // update Database
            $queryBuilder->update('oxorder')
                ->set($field, ':value')
                ->where($queryBuilder->expr()->eq('oxid', ':oxid'))
                ->setParameters([
                    'oxid'  => $oxId,
                    'value' => $value
                ])->execute();

            // update Object
            $this->assign([
                $field => $value
            ]);
        } catch (Exception) {
            // do nothing
        }
    }
}
