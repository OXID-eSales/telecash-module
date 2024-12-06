<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;

use Exception;
use OxidSolutionCatalysts\TeleCash\IPG\API\Request\Transaction;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Error;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Order\Sell;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashConstants;

class PostAuthOrder extends Transaction
{
    public function __construct(OrderService $service, string $orderId, string $currency, string $chargeTotal)
    {
        parent::__construct($service);

        $creditCard = $this->document->createElement(
            TeleCashConstants::PREF_V1 . 'CreditCardTxType'
        );
        $creditCard->appendChild($this->document->createElement(
            TeleCashConstants::PREF_V1 . 'Type',
            'postAuth'
        ));

        $payment = $this->document->createElement(
            TeleCashConstants::PREF_V1 . 'Payment'
        );
        $payment->appendChild($this->document->createElement(
            TeleCashConstants::PREF_V1 . 'ChargeTotal',
            $chargeTotal
        ));
        $payment->appendChild($this->document->createElement(
            TeleCashConstants::PREF_V1 . 'Currency',
            $currency
        ));

        $transactionDetails = $this->document->createElement(
            TeleCashConstants::PREF_V1 . 'TransactionDetails'
        );
        $transactionDetails->appendChild($this->document->createElement(
            TeleCashConstants::PREF_V1 . 'OrderId',
            $orderId
        ));

        $item0 = $this->element->getElementsByTagName(TeleCashConstants::PREF_V1 . 'Transaction')
            ->item(0);

        if ($item0) {
            $item0->appendChild($creditCard);
            $item0->appendChild($payment);
            $item0->appendChild($transactionDetails);
        }
    }

    /**
     * @return Sell|Error
     * @throws Exception
     */
    public function postAuth(): Sell|Error
    {
        $response = $this->service->IPGApiOrder($this);

        return $response instanceof Error ? $response : new Sell($response);
    }
}
