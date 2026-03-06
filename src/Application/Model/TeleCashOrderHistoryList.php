<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Application\Model;

use Doctrine\DBAL\Driver\Connection;
use OxidEsales\Eshop\Core\Model\ListModel;
use OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionProviderInterface;
use OxidSolutionCatalysts\TeleCash\Traits\ServiceContainer;

class TeleCashOrderHistoryList extends ListModel
{
    use ServiceContainer;

    protected Connection $connection;

    /**
     * List Object class name
     *
     * @var string
     */
    protected $_sObjectsInListName = TeleCashOrderHistory::class;

    /**
     * Class Constructor
     *
     * @param string|null $sObjectName Associated list item object type
     * @param Connection|null $connection
     * @param bool $initParent
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function __construct(
        string $sObjectName = null,
        ?Connection $connection = null,
        bool $initParent = true
    ) {
        if ($initParent) {
            parent::__construct($sObjectName);
        }

        $this->setContainer($this->getContainer());

        if ($connection !== null) {
            $this->connection = $connection;
        } else {
            $connectionProvider = $this->getRequiredService(
                ConnectionProviderInterface::class,
                'ConnectionProviderInterface'
            );
            $this->connection = $connectionProvider->get();
        }
    }

    /**
     * @param string $oId
     * @param string $orderDirection
     * @return void
     */
    public function getTeleCashOrderHistoryList(string $oId, string $orderDirection = 'asc'): void
    {
        $orderDirection = in_array(strtolower($orderDirection), ['asc', 'desc'], true)
            ? $orderDirection
            : 'asc';

        $oBaseObject = $this->getBaseObject();
        $sFields = $oBaseObject->getSelectFields();
        $sViewName = $oBaseObject->getViewName();

        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = $this->connection;

        $select = "select " . $sFields . "
            from " . $sViewName . "
            where " . $sViewName . "." .
            $connection->quoteIdentifier('oid') . " = :oid
            order by " . $sViewName . "." .
            $connection->quoteIdentifier('oxtimestamp') . " " . $orderDirection;

        $this->selectString($select, [
            ':oid' => $oId
        ]);
    }
}
