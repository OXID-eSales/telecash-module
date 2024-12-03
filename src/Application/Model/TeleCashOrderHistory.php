<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Application\Model;

use DateTime;
use InvalidArgumentException;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidSolutionCatalysts\TeleCash\Application\Model\Interface\TeleCashOrderHistoryInterface;
use OxidSolutionCatalysts\TeleCash\Core\Module;
use OxidSolutionCatalysts\TeleCash\IPG\Model\TransactionResult;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashCurrency;
use OxidSolutionCatalysts\TeleCash\Traits\DataGetter;
use OxidSolutionCatalysts\TeleCash\Traits\Json;
use OxidSolutionCatalysts\TeleCash\Traits\ServiceContainer;

class TeleCashOrderHistory extends BaseModel implements TeleCashOrderHistoryInterface
{
    use ServiceContainer;
    use DataGetter;
    use Json;

    protected string $oId = '';

    protected TransactionResult $transactionResult;

    /** Helper Class for Currency Handling */
    protected TeleCashCurrency $teleCashCurrency;

    protected $_sClassName = self::class;
    protected $_sCoreTable = Module::TELECASH_ORDER_HISTORY_EXTENSION_TABLE;

    /**
     * Constructor for TeleCashOrder.
     *
     * Initializes a new instance of the TeleCashPayment class. This constructor can be used
     * in both production and test environments due to its flexible parameter configuration.
     *
     * @param bool           $initParent  Whether to initialize the parent BaseModel.
     *                                    Set to false in test environment to avoid
     *                                    OXID framework dependencies. Default is true.
     */
    public function __construct(
        bool $initParent = true
    ) {
        if ($initParent) {
            parent::__construct();
        }

        $this->setContainer($this->getContainer());
        $this->init($this->_sCoreTable);

        $this->teleCashCurrency = new TeleCashCurrency();
        $this->transactionResult = new TransactionResult([]);
    }

    /**
     * OXID Core
     * *
     * * {@inheritDoc}
 *
     * @param string $oxid Object ID
     *
     * @return bool
     */
    public function load($oxid)
    {
        $result = parent::load($oxid);
        $this->loadTransactionResultFromDb();
        return $result;
    }

    /**
     * Set the TeleCash Transaction Result
     * @param array<string, string> $transactionData
     */
    public function setTransactionResult(array $transactionData): void
    {
        $this->transactionResult = new TransactionResult($transactionData);
        $this->oId = (string) $this->transactionResult->getValue('oid');
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
     * get the Txn DateTime as DateTime-Object
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function getTxnDateTime(): ?DateTime
    {
        $txnDateTime = (string) $this->transactionResult->getValue('txndatetime');
        $result = DateTime::createFromFormat('Y:m:d-H:i:s', $txnDateTime);
        return $result ?: null;
    }

    /**
     * get the Txn DateTime as formated string
     */
    public function getTxnDate(): string
    {
        $dateTime = $this->getTxnDateTime();
        return $dateTime ? $dateTime->format('d.m.Y H:i:s') : '';
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
            Module::TELECASH_ORDER_HISTORY_EXTENSION_TABLE_OID      => $this->oId,
            Module::TELECASH_ORDER_HISTORY_EXTENSION_TABLE_RESPONSE => $dataAsString
        ];
        $this->assign($params);

        return parent::save();
    }

    /** load the transaction */
    public function loadTransactionResultFromDb(): void
    {
        $data = $this->getFieldStringData(Module::TELECASH_ORDER_HISTORY_EXTENSION_TABLE_RESPONSE);

        if (!empty($data)) {
            /** @var array<string, string> $transactionData */
            $transactionData = $this->jsonToArray($data);
            $this->transactionResult = new TransactionResult($transactionData);
        }
    }
}
