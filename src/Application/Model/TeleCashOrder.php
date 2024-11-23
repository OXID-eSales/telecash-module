<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Application\Model;

use InvalidArgumentException;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidSolutionCatalysts\TeleCash\Application\Model\Interface\TeleCashOrderInterface;
use OxidSolutionCatalysts\TeleCash\Core\Module;
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

    protected $_sClassName = 'OxidSolutionCatalysts\TeleCash\Application\TeleCashOrder';
    protected $_sCoreTable = Module::TELECASH_ORDER_EXTENSION_TABLE;

    protected TransactionResult $transactionResult;

    protected string $oxOrderId = '';

    /**
     * Helper Class for Currency Handling
     */
    protected TeleCashCurrency $teleCashCurrency;

    /**
     * Constructor for TeleCashOrder.
     *
     * Initializes a new instance of the TeleCashPayment class. This constructor can be used
     * in both production and test environments due to its flexible parameter configuration.
     *
     * @param bool                           $initParent  Whether to initialize the parent BaseModel.
     *                                                    Set to false in test environment to avoid
     *                                                    OXID framework dependencies. Default is true.
     */
    public function __construct(
        string $oxOrderId,
        bool $initParent = true
    ) {
        if ($initParent) {
            parent::__construct();
        }

        $this->oxOrderId = $oxOrderId;
        $this->teleCashCurrency = new TeleCashCurrency();
        $this->transactionResult = new TransactionResult([]);

        $this->init($this->_sCoreTable);
        $this->setContainer($this->getContainer());
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

    /** get the Txn DateTime */
    public function getTxnDateTime(): string
    {
        return (string) $this->transactionResult->getValue('txndatetime');
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
        $chargeTotalString = $this->transactionResult->getValue('chargetotal');
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
        $data = $this->getFieldStringData(Module::TELECASH_ORDER_EXTENSION_TABLE_RESPONSE);

        if (!empty($data)) {
            /** @var array<string, string> $transactionData */
            $transactionData = $this->jsonToArray($data);
            $this->transactionResult = new TransactionResult($transactionData);
        }
    }
}
