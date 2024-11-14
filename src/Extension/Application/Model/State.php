<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Extension\Application\Model;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionProviderInterface;
use OxidSolutionCatalysts\TeleCash\Exception\TeleCashException;
use OxidSolutionCatalysts\TeleCash\Traits\DataGetter;
use OxidSolutionCatalysts\TeleCash\Traits\ServiceContainer;

class State extends State_parent
{
    use DataGetter;
    use ServiceContainer;

    /**
     * @throws StandardException
     * @throws DatabaseConnectionException
     * @throws Exception
     */
    public function loadByIdAndCountry(string $oxIsoAlpha2, string $countryID): bool
    {
        // must mimic the original "load" functionality
        $query = $this->buildSelectString([
            $this->getViewName() . '.oxisoalpha2' => $oxIsoAlpha2,
            $this->getViewName() . '.oxcountryid' => $countryID
        ]);

        $connection = $this->getConnectionProvider();
        $data = (array) $connection->fetchAssociative($query);

        $this->assign($data);
        $this->_isLoaded = true;

        return $this->_isLoaded;
    }

    /**
     * @throws StandardException
     */
    private function getConnectionProvider(): Connection
    {
        $connectionProvider = $this->getServiceFromContainer(ConnectionProviderInterface::class);
        if (!$connectionProvider) {
            throw (new TeleCashException())->serviceNotFound();
        }
        return $connectionProvider->get();
    }
}
