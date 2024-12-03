<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Application\Model;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\ForwardCompatibility\Result;
use Doctrine\DBAL\Query\QueryBuilder;
use OxidEsales\Eshop\Core\Model\ListModel;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidSolutionCatalysts\TeleCash\Core\Module;
use OxidSolutionCatalysts\TeleCash\Traits\ServiceContainer;

class TeleCashOrderHistoryList extends ListModel
{
    use ServiceContainer;

    /**
     * List Object class name
     *
     * @var string
     */
    protected $_sObjectsInListName = TeleCashOrderHistory::class;

    private QueryBuilderFactoryInterface $queryBuilderFactory;

    /**
     * Class Constructor
     *
     * @param string $sObjectName Associated list item object type
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function __construct(
        $sObjectName = null,
        bool $initParent = true
    ) {
        if ($initParent) {
            parent::__construct($sObjectName);
        }

        $this->setContainer($this->getContainer());
        $queryBuilderFactory = $this->getServiceFromContainer(QueryBuilderFactoryInterface::class);
        if ($queryBuilderFactory) {
            $this->queryBuilderFactory = $queryBuilderFactory;
        }
    }

    /**
     * @param string $oId
     * @param string $orderDirection
     * @return void
     * @throws Exception|\Doctrine\DBAL\Driver\Exception
     */
    public function getTeleCashOrderHistoryList(string $oId, string $orderDirection = 'asc'): void
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->queryBuilderFactory->create();

        $listObject = $this->getBaseObject();

        $queryBuilder->select($listObject->getSelectFields())
            ->from(Module::TELECASH_ORDER_HISTORY_EXTENSION_TABLE)
            ->where('oid = :oid')
            ->orderBy('oxtimestamp', $orderDirection);

        $parameters = [
            'oid' => $oId
        ];

        $resultDB = $queryBuilder->setParameters($parameters)
            ->execute();

        if (is_object($resultDB) && is_a($resultDB, Result::class)) {
            $dbData = $resultDB->fetchAllAssociative();
            $this->assignArray($dbData);
        }
    }
}
