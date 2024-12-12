<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Application\Model;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use InvalidArgumentException;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionProviderInterface;
use OxidSolutionCatalysts\TeleCash\Application\Model\Interface\TeleCashOrderInterface;
use OxidSolutionCatalysts\TeleCash\Core\Module;
use OxidSolutionCatalysts\TeleCash\Exception\TeleCashException;
use OxidSolutionCatalysts\TeleCash\IPG\Model\TransactionResult;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashCurrency;
use OxidSolutionCatalysts\TeleCash\Traits\DataGetter;
use OxidSolutionCatalysts\TeleCash\Traits\Json;
use OxidSolutionCatalysts\TeleCash\Traits\ModelGetter;
use OxidSolutionCatalysts\TeleCash\Traits\ServiceContainer;

class TeleCashOrder extends BaseModel implements TeleCashOrderInterface
{
    use ServiceContainer;
    use DataGetter;
    use ModelGetter;
    use Json;

    protected string $oxOrderId = '';

    protected Connection $connection;
    protected TransactionResult $transactionResult;

    /** Helper Class for Currency Handling */
    protected TeleCashCurrency $teleCashCurrency;

    protected ?TeleCashOrderHistoryList $teleCashOrderHistoryList = null;

    protected $_sClassName = self::class;
    protected $_sCoreTable = Module::TELECASH_ORDER_EXTENSION_TABLE;

    protected ?bool $teleCashOrderHistoryIsLoaded = null;

    /**
     * Constructor for TeleCashOrder.
     *
     * Initializes a new instance of the TeleCashPayment class. This constructor can be used
     * in both production and test environments due to its flexible parameter configuration.
     *
     * @param Connection|null $connection Optional database connection. If null, the connection
     *                                    will be retrieved from the service container.
     *                                    Primarily used for testing.
     * @param bool           $initParent  Whether to initialize the parent BaseModel.
     *                                    Set to false in test environment to avoid
     *                                    OXID framework dependencies. Default is true.
     */
    public function __construct(
        ?Connection $connection = null,
        bool $initParent = true
    ) {
        if ($initParent) {
            parent::__construct();
        }

        $this->setContainer($this->getContainer());
        $this->init($this->_sCoreTable);

        $this->teleCashCurrency = new TeleCashCurrency();
        $this->transactionResult = new TransactionResult([]);

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
     * Loads TeleCash-Order by using orderid instead of oxid.
     *
     * @param string $orderId content load ID
     *
     * @return bool
     */
    public function loadByOrderId(string $orderId = ''): bool
    {
        if (!$orderId) {
            return false;
        }

        //getting at least one field before lazy loading the object
        $this->addField('oxid', 0);

        $table = $this->getViewName();

        $query = $this->buildSelectString([
            $table . '.' . Module::TELECASH_ORDER_EXTENSION_TABLE_OXORDERID => $orderId
        ]);

        try {
            $result = $this->connection->fetchAssociative($query);
            if ($result !== false && is_array($result)) {
                $this->assign($result);
                $this->_isLoaded = true;
                $this->setOxOrderId($orderId);
            }
        } catch (Exception) {
            $this->_isLoaded = false;
        }

        return $this->isLoaded();
    }

    public function setOxOrderId(string $oxOrderId): void
    {
        $this->oxOrderId = $oxOrderId;
    }

    /**
     * Set the TeleCash Transaction Result
     * @param array<string, string> $transactionData
     */
    public function setTransactionResult(array $transactionData): void
    {
        $this->_isLoaded = false;
        $this->transactionResult = new TransactionResult($transactionData);
    }

    /** get the TxnType */
    public function getTxnType(): string
    {
        $txnType = $this->getValue(
            Module::TELECASH_ORDER_EXTENSION_TABLE_TXNTYPE,
            'txntype'
        );

        // validate TxnType
        if (!in_array($txnType, Module::TELECASH_POSSIBLE_TRANSACTION_TYPES, true)) {
            $txnType = '';
        }
        return $txnType;
    }

    /** get the Oid */
    public function getOid(): string
    {
        return $this->getValue(
            Module::TELECASH_ORDER_EXTENSION_TABLE_OID,
            'oid'
        );
    }

    /** get the Currency */
    public function getCurrency(): string
    {
        $currency = $this->getValue(
            Module::TELECASH_ORDER_EXTENSION_TABLE_CURRENCY,
            'currency'
        );
        if ($this->isLoaded()) {
            $currency = $this->getFieldStringData(Module::TELECASH_ORDER_EXTENSION_TABLE_CURRENCY);
        }
        return $currency;
    }

    /** get the Currency in OXID-Style */
    public function getOxidCurrency(): string
    {
        $currency = $this->getCurrency();

        // validate Currency
        if (empty($currency)) {
            return '';
        }

        try {
            return $this->teleCashCurrency->getShortnameByCurrencyCode($currency);
        } catch (InvalidArgumentException) {
            return '';
        }
    }

    /** get the Charge Total */
    public function getChargeTotal(): string
    {
        if ($this->isLoaded()) {
            $chargeTotal = $this->getFieldFloatData(Module::TELECASH_ORDER_EXTENSION_TABLE_CHARGETOTAL);
            return number_format($chargeTotal, 2, '.', '');
        }
        $chargeTotal = (string) $this->transactionResult->getValue('chargetotal');
        // Standardize decimal separator to dot
        $chargeTotal = str_replace(',', '.', $chargeTotal);
        return is_numeric($chargeTotal) ? number_format((float)$chargeTotal, 2, '.', '') : '0.00';
    }

    /** get the Charge Total in OXID Style (as float) */
    public function getOxidChargeTotal(): float
    {
        if ($this->isLoaded()) {
            return $this->getFieldFloatData(Module::TELECASH_ORDER_EXTENSION_TABLE_CHARGETOTAL);
        }
        $chargeTotalString = (string) $this->transactionResult->getValue('chargetotal');
        // bulletproof because is_numeric does not recognize that German commas are numeric
        $chargeTotalString = str_replace(',', '.', $chargeTotalString);

        return is_numeric($chargeTotalString) ? (float) $chargeTotalString : 0.0;
    }

    /**
     * @throws TeleCashException
     */
    public function getPossibleCharge(): float
    {
        $result = $this->getOxidChargeTotal();

        // collect postAuth from History
        $teleCashOrderHistoryList = $this->getTeleCashOrderHistoryList();
        if ($teleCashOrderHistoryList && $teleCashOrderHistoryList->count()) {
            foreach ($teleCashOrderHistoryList as $teleCashOrderHistoryEntry) {
                /** @var TeleCashOrderHistory $teleCashOrderHistoryEntry */
                if ($teleCashOrderHistoryEntry->getTxnType() === Module::TELECASH_TXN_TYPE_POSTAUTH) {
                    $result -= $teleCashOrderHistoryEntry->getOxidChargeTotal();
                }
            }
        }
        return $result;
    }

    /**
     * get IpgTransactionId - This ID is not persisted separately in the DB.
     * That's why I only get it from the TransactionData
     */
    public function getIpgTransactionId(): string
    {
        return (string) $this->transactionResult->getValue('ipgTransactionId');
    }

    /** get the Status translated in transaction-language */
    public function getStatus(): string
    {
        return $this->getValue(
            Module::TELECASH_ORDER_EXTENSION_TABLE_STATUS,
            'status'
        );
    }

    /** get the used Payment Method */
    public function getPaymentMethod(): string
    {
        if ($this->isLoaded()) {
            return $this->getFieldStringData(Module::TELECASH_ORDER_EXTENSION_TABLE_PAYMENTMETHOD);
        }

        $paymentMethod = (string) $this->transactionResult->getValue('paymentMethod');
        if (empty($paymentMethod)) {
            return '';
        }

        $flippedPaymentMethods = array_flip(Module::TELECASH_PAYMENT_IDENTS);
        // validate PaymentMethod
        return array_key_exists($paymentMethod, $flippedPaymentMethods)
            ? $flippedPaymentMethods[$paymentMethod]
            : '';
    }

    /**
     * @throws TeleCashException
     */
    public function getTeleCashOrderHistoryList(): ?TeleCashOrderHistoryList
    {
        if (is_null($this->teleCashOrderHistoryIsLoaded)) {
            $this->teleCashOrderHistoryIsLoaded = false;
            $teleCashOrderHistoryList = $this->getOxNewService()->oxNew(TeleCashOrderHistoryList::class);
            $teleCashOrderHistoryList->getTeleCashOrderHistoryList($this->getOid());
            if ($teleCashOrderHistoryList->count()) {
                $this->teleCashOrderHistoryIsLoaded = true;
                $this->teleCashOrderHistoryList = $teleCashOrderHistoryList;
            }
        }
        return $this->teleCashOrderHistoryList;
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

        if ($oxid) {
            $teleCashOrderHistoryList = $this->getTeleCashOrderHistoryList();
            if ($teleCashOrderHistoryList) {
                foreach ($teleCashOrderHistoryList as $teleCashOrderHistory) {
                    /** @var TeleCashOrderHistory $teleCashOrderHistory */
                    $teleCashOrderHistory->delete();
                }
            }
        }

        return parent::delete($oxid);
    }

    /**
     * OXID Core
     *
     * {@inheritDoc}
     *
     * @return string|bool
     */
    public function save()
    {
        // save to History
        $teleCashOrderHistory = $this->getTeleCashOrderHistoryModel();
        $teleCashOrderHistory->setTransactionResult($this->transactionResult->toArray());
        $teleCashOrderHistory->save();

        // save to Model
        $params = [
            Module::TELECASH_ORDER_EXTENSION_TABLE_OXORDERID => $this->oxOrderId,
            Module::TELECASH_ORDER_EXTENSION_TABLE_OID => $this->getOid(),
            Module::TELECASH_ORDER_EXTENSION_TABLE_STATUS => $this->getStatus(),
            Module::TELECASH_ORDER_EXTENSION_TABLE_CURRENCY => $this->getCurrency(),
            Module::TELECASH_ORDER_EXTENSION_TABLE_TXNTYPE => $this->getTxnType(),
            Module::TELECASH_ORDER_EXTENSION_TABLE_PAYMENTMETHOD => $this->getPaymentMethod(),
            Module::TELECASH_ORDER_EXTENSION_TABLE_CHARGETOTAL => $this->getOxidChargeTotal(),
        ];
        $this->assign($params);

        return parent::save();
    }

    private function getValue(string $tableColumn, string $fallbackDataItem): string
    {
        if ($this->isLoaded()) {
            return $this->getFieldStringData($tableColumn);
        }
        return (string) $this->transactionResult->getValue($fallbackDataItem);
    }
}
