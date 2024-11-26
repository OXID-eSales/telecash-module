<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Application\Model;

use DateTime;
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
use OxidSolutionCatalysts\TeleCash\Traits\ServiceContainer;

class TeleCashOrder extends BaseModel implements TeleCashOrderInterface
{
    use ServiceContainer;
    use DataGetter;
    use Json;

    protected string $oxOrderId = '';

    protected Connection $connection;
    protected TransactionResult $transactionResult;

    /** Helper Class for Currency Handling */
    protected TeleCashCurrency $teleCashCurrency;

    protected $_sClassName = 'OxidSolutionCatalysts\TeleCash\Application\TeleCashOrder';
    protected $_sCoreTable = Module::TELECASH_ORDER_EXTENSION_TABLE;

    /**
     * Constructor for TeleCashOrder.
     *
     * Initializes a new instance of the TeleCashPayment class. This constructor can be used
     * in both production and test environments due to its flexible parameter configuration.
     *
     * @param string           $oxOrderId orderID from OXID
     * @param Connection|null $connection Optional database connection. If null, the connection
     *                                    will be retrieved from the service container.
     *                                    Primarily used for testing.
     * @param bool           $initParent  Whether to initialize the parent BaseModel.
     *                                    Set to false in test environment to avoid
     *                                    OXID framework dependencies. Default is true.
     */
    public function __construct(
        string $oxOrderId,
        ?Connection $connection = null,
        bool $initParent = true
    ) {
        if ($initParent) {
            parent::__construct();
        }

        $this->setContainer($this->getContainer());
        $this->init($this->_sCoreTable);

        $this->oxOrderId = $oxOrderId;
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
        $orderId = $orderId ?: $this->oxOrderId;

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
            }
        } catch (Exception) {
            $this->_isLoaded = false;
        }

        return $this->isLoaded();
    }

    /**
     * Set the TeleCash Transaction Result
     * @param array<string, string> $transactionData
     */
    public function setTransactionResult(array $transactionData): void
    {
        $this->transactionResult = new TransactionResult($transactionData);
    }

    /** get the TxnType */
    public function getTxnType(): string
    {
        $txnType = $this->transactionResult->getValue('txntype');
        // validate TxnType
        if (!in_array($txnType, Module::TELECASH_TRANSACTION_TYPES, true)) {
            $txnType = '';
        }
        return $txnType;
    }

    /**
     * get the Txn DateTime
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function getTxnDateTime(): ?DateTime
    {
        $txnDateTime = (string) $this->transactionResult->getValue('txndatetime');
        $result = DateTime::createFromFormat('Y:m:d-H:i:s', $txnDateTime);
        return $result ?: null;
    }

    /** get the Oid */
    public function getOid(): string
    {
        return (string) $this->transactionResult->getValue('oid');
    }

    /** get the EndpointTransactionId */
    public function getEndpointTransactionId(): string
    {
        return (string) $this->transactionResult->getValue('endpointTransactionId');
    }

    /** get the Terminal ID */
    public function getTerminalId(): string
    {
        return (string) $this->transactionResult->getValue('terminal_id');
    }

    /** get the IPG Transaction ID */
    public function getIpgTransactionId(): string
    {
        return (string) $this->transactionResult->getValue('ipgTransactionId');
    }

    /** get the Currency */
    public function getCurrency(): string
    {
        $currency = (string) $this->transactionResult->getValue('currency');
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

    /** get the EndpointTransactionId */
    public function getChargeTotal(): float
    {
        $chargeTotalString = (string) $this->transactionResult->getValue('chargetotal');
        // bulletproof because is_numeric does not recognize that German commas are numeric
        $chargeTotalString = str_replace(',', '.', $chargeTotalString);
        return is_numeric($chargeTotalString) ? (float) $chargeTotalString : 0.0;
    }

    /** get the Status translated in transaction-language */
    public function getStatus(): string
    {
        return (string) $this->transactionResult->getValue('status');
    }

    /** get the Status as Code, named in TeleCash as ProcessorResponseCode */
    public function getProcessorResponseCode(): string
    {
        return (string) $this->transactionResult->getValue('processor_response_code');
    }

    /** get the used Payment Method */
    public function getPaymentMethod(): string
    {
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
     * OXID Core
     *
     * {@inheritDoc}
     *
     * @return string|bool
     */
    public function save()
    {
        $data = $this->transactionResult->toArray();
        $dataAsString = $this->arrayToJson($data);

        $params = [
            Module::TELECASH_ORDER_EXTENSION_TABLE_OXORDERID => $this->oxOrderId,
            Module::TELECASH_ORDER_EXTENSION_TABLE_RESPONSE  => $dataAsString
        ];
        $this->assign($params);

        return parent::save();
    }

    /** load the transaction */
    public function loadTransactionResultFromDb(): void
    {
        if ($this->oxOrderId && !$this->isLoaded()) {
            $this->loadByOrderId();
        }
        $data = $this->getFieldStringData(Module::TELECASH_ORDER_EXTENSION_TABLE_RESPONSE);

        if (!empty($data)) {
            /** @var array<string, string> $transactionData */
            $transactionData = $this->jsonToArray($data);
            $this->transactionResult = new TransactionResult($transactionData);
        }
    }
}
