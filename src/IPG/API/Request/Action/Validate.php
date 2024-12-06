<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;

use DOMException;
use Exception;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\CreditCardData;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\Payment;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model\TransactionDetails;
use OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Error;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action\Validation;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashConstants;

/**
 * Class Validate
 */
class Validate extends Action
{
    /**
     * @param OrderService $service
     * @param CreditCardData $creditCardData
     * @param float $amount
     * @param string|null $text
     * @throws DOMException
     */
    public function __construct(
        OrderService $service,
        CreditCardData $creditCardData,
        float $amount = 1.0,
        ?string $text = null
    ) {
        parent::__construct($service);

        $xml    = $this->document->createElement(TeleCashConstants::PREF_A1 . 'Validate');
        $ccData = $creditCardData->getXML($this->document);
        $xml->appendChild($ccData);

        if ($amount > 1.0) {
            $payment     = new Payment(null, $amount);
            $paymentData = $payment->getXML($this->document);
            $xml->appendChild($paymentData);
        }

        if (!empty($text)) {
            $transactionDetails = new TransactionDetails(TeleCashConstants::A1, $text);
            $transactionDetailsData = $transactionDetails->getXML($this->document);
            $xml->appendChild($transactionDetailsData);
        }

        $item0 = $this->element->getElementsByTagName(TeleCashConstants::PREF_A1 . 'Action')->item(0);
        $item0?->appendChild($xml);
    }

    /**
     * @return Validation|Error
     * @throws Exception
     */
    public function validate(): Validation|Error
    {
        $response = $this->service->IPGApiAction($this);

        return $response instanceof Error ? $response : new Validation($response);
    }
}
