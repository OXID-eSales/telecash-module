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

    protected bool $transactionResultIsLoaded = false;

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
     * Set the TeleCash Transaction Result
     * @param array<string, string> $transactionData
     */
    public function setTransactionResult(array $transactionData): void
    {
        $this->transactionResultIsLoaded = true;
        $this->transactionResult = new TransactionResult($transactionData);
        $this->oId = $this->getValue('oid');
    }

    /** get the TxnType */
    public function getTxnType(): string
    {
        $txnType = $this->getValue('txntype');
        // validate TxnType
        if (!in_array($txnType, Module::TELECASH_POSSIBLE_TRANSACTION_TYPES, true)) {
            $txnType = '';
        }
        return $txnType;
    }

    /**
     * get the OId
     */
    public function getOid(): string
    {
        return $this->oId ?: $this->getValue('oid');
    }

    /**
     * get the Txn DateTime as DateTime-Object
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function getTxnDateTime(): ?DateTime
    {
        $txnDateTime = $this->getValue('txndatetime');
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
        return $this->getValue('endpointTransactionId');
    }

    /** get the Terminal ID */
    public function getTerminalId(): string
    {
        return $this->getValue('terminal_id');
    }

    /** get the IPG Transaction ID */
    public function getIpgTransactionId(): string
    {
        return $this->getValue('ipgTransactionId');
    }

    /** get the Currency */
    public function getCurrency(): string
    {
        $currency = $this->getValue('currency');
        // validate Currency
        if (!is_numeric($currency)) {
            return '';
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
        $chargeTotal = $this->getValue('chargetotal');
        // bulletproof because is_numeric does not recognize that German commas are numeric
        $chargeTotal = str_replace(',', '.', $chargeTotal);
        return is_numeric($chargeTotal) ? number_format((float)$chargeTotal, 2, '.', '') : '0.00';
    }

    /** get the Charge Total in OXID Style (float) */
    public function getOxidChargeTotal(): float
    {
        $chargeTotalString = $this->getChargeTotal();
        return (float)$chargeTotalString;
    }

    /** get the Status translated in transaction-language */
    public function getStatus(): string
    {
        return $this->getValue('status');
    }

    /** get the Status as Code, named in TeleCash as ProcessorResponseCode */
    public function getProcessorResponseCode(): string
    {
        return $this->getValue('processor_response_code');
    }

    /** get the used Payment Method */
    public function getPaymentMethod(): string
    {
        $paymentMethod = $this->getValue('paymentMethod');
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
        $this->transactionResultIsLoaded = true;
        $data = $this->getFieldStringData(Module::TELECASH_ORDER_HISTORY_EXTENSION_TABLE_RESPONSE);
        if (!empty($data)) {
            /** @var array<string, string> $transactionData */
            $transactionData = $this->jsonToArray($data);
            $this->transactionResult = new TransactionResult($transactionData);
        }
    }

    private function getValue(string $item): string
    {
        if (!$this->transactionResultIsLoaded) {
            $this->loadTransactionResultFromDb();
        }
        return (string) $this->transactionResult->getValue($item);
    }
}
