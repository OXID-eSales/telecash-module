<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Core\Service;

use Doctrine\DBAL\Driver\ResultStatement;
use Doctrine\DBAL\Exception;
use OxidEsales\Eshop\Core\TableViewNameGenerator;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidSolutionCatalysts\TeleCash\Exception\TeleCashException;
use OxidSolutionCatalysts\TeleCash\Core\Module;

class TeleCashPaymentValidatorService implements TeleCashPaymentValidatorServiceInterface
{
    public function __construct(
        private readonly OxNewService $oxNewService,
        private readonly RegistryService $registryService,
        private readonly QueryBuilderFactoryInterface $queryBuilderFactory
    ) {
    }

    /**
     * @throws TeleCashException
     */
    public function checkIfPaymentExists(
        string $paymentId,
        string $ident,
        string $captureType
    ): bool {
        $value = '';
        try {
            $viewName = $this->oxNewService
                ->oxNew(TableViewNameGenerator::class)
                ->getViewName(Module::TELECASH_PAYMENT_EXTENSION_TABLE);

            $queryBuilder = $this->queryBuilderFactory->create();

            $rawResult = $queryBuilder->select('oxid')
                ->from($viewName)
                ->where($queryBuilder->expr()->eq(
                    Module::TELECASH_PAYMENT_EXTENSION_TABLE_OXPAYMENTID,
                    ':' . Module::TELECASH_PAYMENT_EXTENSION_TABLE_OXPAYMENTID
                ))
                ->andWhere($queryBuilder->expr()->eq(
                    Module::TELECASH_PAYMENT_EXTENSION_TABLE_IDENT,
                    ':' . Module::TELECASH_PAYMENT_EXTENSION_TABLE_IDENT
                ))
                ->andWhere($queryBuilder->expr()->eq(
                    Module::TELECASH_PAYMENT_EXTENSION_TABLE_CAPTURETYPE,
                    ':' . Module::TELECASH_PAYMENT_EXTENSION_TABLE_CAPTURETYPE
                ))
                ->andWhere($queryBuilder->expr()->eq('oxshopid', ':oxshopid'))
                ->setMaxResults(1)
                ->setParameters([
                    ':' . Module::TELECASH_PAYMENT_EXTENSION_TABLE_OXPAYMENTID => $paymentId,
                    ':' . Module::TELECASH_PAYMENT_EXTENSION_TABLE_IDENT       => $ident,
                    ':' . Module::TELECASH_PAYMENT_EXTENSION_TABLE_CAPTURETYPE => $captureType,
                    ':oxshopid' => $this->registryService->getConfig()->getShopId()
                ])
                ->execute();

            if ($rawResult instanceof ResultStatement) {
                $result = $rawResult->fetchOne();
                $value = is_string($result) ? $result : '';
            }
        } catch (Exception | \Doctrine\DBAL\Driver\Exception) {
            throw (new TeleCashException())->checkIfTeleCashPaymentExistsFail();
        }

        return !$value;
    }
}
