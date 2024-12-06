<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request\Transaction;

use DOMException;
use Exception;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\BillingData;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\DirectDebitData;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\Payment;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\TransactionDetails;
use OxidSolutionCatalysts\TeleCash\IPG\API\Request\Transaction;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Error;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Order\Sell as OrderSell;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashConstants;

class SellDirectDebit extends Transaction
{
    /**
     * @param OrderService $service
     * @param DirectDebitData $ddData
     * @param Payment $payment
     * @param BillingData|null $billingData
     * @param TransactionDetails|null $transactionDetails
     * @throws DOMException
     */
    public function __construct(
        OrderService $service,
        DirectDebitData $ddData,
        Payment $payment,
        BillingData|null $billingData,
        TransactionDetails|null $transactionDetails = null
    ) {
        parent::__construct($service);

        $ddTxType = $this->document->createElement(TeleCashConstants::PREF_V1 . 'DE_DirectDebitTxType');
        $ddType   = $this->document->createElement(TeleCashConstants::PREF_V1 . 'Type');
        $ddType->nodeValue = 'sale';
        $ddTxType->appendChild($ddType);

        $paymentData = $payment->getXML($this->document);
        $transActElem = $this->getTransactionElement();
        if ($transActElem) {
            $transActElem->appendChild($ddTxType);
            $ddData->setNamespaceShort('ns1');
            $transActElem->appendChild($ddData->getXML($this->document));
            $transActElem->appendChild($paymentData);
            if (null !== $transactionDetails) {
                $transactionDetailsData = $transactionDetails->getXML($this->document);
                $transActElem->appendChild($transactionDetailsData);
            }
            if (null !== $billingData) {
                $transActElem->appendChild($billingData->getXML($this->document));
            }
        }
    }

    /**
     * @return OrderSell|Error
     * @throws Exception
     */
    public function sell(): OrderSell|Error
    {
        $response = $this->service->IPGApiOrder($this);

        return $response instanceof Error ? $response : new OrderSell($response);
    }
}
