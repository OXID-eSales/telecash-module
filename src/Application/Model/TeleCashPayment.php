<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Application\Model;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionProviderInterface;
use OxidSolutionCatalysts\TeleCash\Core\Module;
use OxidSolutionCatalysts\TeleCash\Traits\DataGetter;
use OxidSolutionCatalysts\TeleCash\Traits\ServiceContainer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class TeleCashPayment extends BaseModel
{
    use ServiceContainer;
    use DataGetter;

    protected Connection $connection;

    /**
     * Current class name
     *
     * @var string
     */
    protected $_sClassName = 'OxidSolutionCatalysts\TeleCash\Application\TeleCashPayment';

    /**
     * Core table name
     *
     * @var string
     */
    protected $_sCoreTable = Module::TELECASH_PAYMENT_EXTENSION_TABLE;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct()
    {
        parent::__construct();
        $this->init($this->_sCoreTable);
        $this->connection = $this->getServiceFromContainer(ConnectionProviderInterface::class)->get();
    }

    /**
     * Loads TeleCash-Payment by using paymentid instead of oxid.
     *
     * @param string $paymentId content load ID
     *
     * @return bool
     */
    public function loadByPaymentId(string $paymentId = ''): bool
    {
        //getting at least one field before lazy loading the object
        $this->addField('oxid', 0);

        $table = $this->getViewName();
        $shopId = $this->getShopId();

        $query = $this->buildSelectString([
            $table . '.' . Module::TELECASH_PAYMENT_EXTENSION_TABLE_OXPAYMENTID => $paymentId,
            $table . '.oxshopid'                                                => $shopId
        ]);

        try {
            $result = $this->connection->fetchAssociative($query);
            $this->_isLoaded = is_array($result) && $this->assign($result);
        } catch (Exception) {
            $this->_isLoaded = false;
        }

        return $this->isLoaded();
    }

    /** setter for PaymentId */
    public function setPaymentId(string $paymentId): void
    {
        $this->assign([Module::TELECASH_PAYMENT_EXTENSION_TABLE_OXPAYMENTID => $paymentId]);
    }

    public function getPaymentId(): string
    {
        return $this->getFieldStringData(Module::TELECASH_PAYMENT_EXTENSION_TABLE_OXPAYMENTID);
    }

    public function setTeleCashIdent(string $ident = ''): void
    {
        $ident = in_array($ident, Module::TELECASH_PAYMENT_IDENTS, true) ?
            $ident :
            Module::TELECASH_PAYMENT_IDENT_DEFAULT;

        $this->assign([Module::TELECASH_PAYMENT_EXTENSION_TABLE_IDENT => $ident]);
    }

    public function getTeleCashIdent(): string
    {
        return $this->getFieldStringData(Module::TELECASH_PAYMENT_EXTENSION_TABLE_IDENT);
    }

    public function setTeleCashCaptureType(string $captureType = ''): void
    {
        $captureTypes = Module::TELECASH_CAPTURE_TYPES[$this->getTeleCashIdent()];
        $captureType = in_array($captureType, $captureTypes, true) ?
            $captureType :
            Module::TELECASH_CAPTURE_TYPE_DIRECT;

        $this->assign([Module::TELECASH_PAYMENT_EXTENSION_TABLE_CAPTURETYPE => $captureType]);
    }

    public function getTeleCashCaptureType(): string
    {
        return $this->getFieldStringData(Module::TELECASH_PAYMENT_EXTENSION_TABLE_CAPTURETYPE);
    }
}
