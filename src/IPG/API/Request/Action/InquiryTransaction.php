<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;

use DOMException;
use Exception;
use OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action\Validation;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Error;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashConstants;

class InquiryTransaction extends Action
{
    protected \DOMElement $inquiryTransaction;

    /**
     * @param OrderService $service
     * @throws DOMException
     */
    public function __construct(OrderService $service)
    {
        parent::__construct($service);

        $inquiryTransaction = $this->document->createElement(TeleCashConstants::PREF_A1 . 'InquiryTransaction');
        $item0 = $this->element->getElementsByTagName(TeleCashConstants::PREF_A1 . 'Action')->item(0);
        $item0?->appendChild($inquiryTransaction);
        $this->inquiryTransaction = $inquiryTransaction;
    }

    /**
     * @throws DOMException
     * @throws Exception
     */
    public function getByIPGTransactionId(string $storeId): Validation|Error
    {
        $storeIdElem = $this->document->createElement(TeleCashConstants::PREF_A1 . 'IpgTransactionId');
        $storeIdElem->nodeValue = $storeId;
        $this->inquiryTransaction->appendChild($storeIdElem);

        $response = $this->service->IPGApiAction($this);

        return $response instanceof Error ? $response : new Validation($response);
    }

    /**
     * @throws DOMException
     * @throws Exception
     */
    public function getByOrderIdAndTDate(string $orderId, string $tDate): Validation|Error
    {
        $orderIdElem = $this->document->createElement(TeleCashConstants::PREF_A1 . 'OrderId');
        $orderIdElem->nodeValue = $orderId;
        $this->inquiryTransaction->appendChild($orderIdElem);

        $tDateElem = $this->document->createElement(TeleCashConstants::PREF_A1 . 'TDate');
        $tDateElem ->nodeValue = $tDate;
        $this->inquiryTransaction->appendChild($tDateElem);

        $response = $this->service->IPGApiAction($this);

        return $response instanceof Error ? $response : new Validation($response);
    }
}
