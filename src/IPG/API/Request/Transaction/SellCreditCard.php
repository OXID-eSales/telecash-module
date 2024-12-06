<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request\Transaction;

use DOMException;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\CreditCardData;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\Payment;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\TransactionDetails;
use OxidSolutionCatalysts\TeleCash\IPG\API\Request\Transaction;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Error;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Order\Sell as OrderSell;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashConstants;

class SellCreditCard extends Transaction
{
    /**
     * @param OrderService $service
     * @param CreditCardData $ccData
     * @param Payment $payment
     * @param TransactionDetails|null $transactionDetails
     * @throws DOMException
     */
    public function __construct(
        OrderService $service,
        CreditCardData $ccData,
        Payment $payment,
        TransactionDetails $transactionDetails = null
    ) {
        parent::__construct($service);

        $ccTxType = $this->document->createElement(TeleCashConstants::PREF_V1 . 'CreditCardTxType');
        $ccType   = $this->document->createElement(TeleCashConstants::PREF_V1 . 'Type');
        $ccType->nodeValue = 'sale';
        $ccTxType->appendChild($ccType);

        $paymentData = $payment->getXML($this->document);
        $transActElem = $this->getTransactionElement();
        if ($transActElem) {
            $transActElem->appendChild($ccTxType);
            $ccData->setNamespaceShort('ns1');
            $transActElem->appendChild($ccData->getXML($this->document));
            $transActElem->appendChild($paymentData);
            if (null !== $transactionDetails) {
                $transactionDetailsData = $transactionDetails->getXML($this->document);
                $transActElem->appendChild($transactionDetailsData);
            }
        }
    }

    /**
     * @return OrderSell|Error
     * @throws \Exception
     */
    public function sell(): OrderSell|Error
    {
        $response = $this->service->IPGApiOrder($this);

        return $response instanceof Error ? $response : new OrderSell($response);
    }
}
