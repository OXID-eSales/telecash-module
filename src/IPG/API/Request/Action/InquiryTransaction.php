<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;

use OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action\Validation;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Error;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;

class InquiryTransaction extends Action
{
    protected \DOMElement $inquiryTransaction;
    /**
     * @param OrderService            $service
     */
    public function __construct(OrderService $service)
    {
        parent::__construct($service);

        $inquiryTransaction = $this->document->createElement('ns2:InquiryTransaction');
        $item0 = $this->element->getElementsByTagName('ns2:Action')->item(0);
        if ($item0) {
            $item0->appendChild($inquiryTransaction);
        }
        $this->inquiryTransaction = $inquiryTransaction;
    }

    public function getByIPGTransactionId(string $storeId): Validation|Error
    {
        $storeIdElem = $this->document->createElement('ns2:IpgTransactionId');
        $storeIdElem->nodeValue = $storeId;
        $this->inquiryTransaction->appendChild($storeIdElem);

        $response = $this->service->IPGApiAction($this);

       // $xxx = new \OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action\TransactionValues($response);


        return $response instanceof Error ? $response : new Validation($response);
    }

    public function getByOrderIdAndTDate(string $orderId, string $tDate): Validation|Error
    {
        $orderIdElem = $this->document->createElement('ns2:OrderId');
        $orderIdElem->nodeValue = $orderId;
        $this->inquiryTransaction->appendChild($orderIdElem);

        $tDateElem = $this->document->createElement('ns2:TDate');
        $tDateElem ->nodeValue = $tDate;
        $this->inquiryTransaction->appendChild($tDateElem);

        $response = $this->service->IPGApiAction($this);

        return $response instanceof Error ? $response : new Validation($response);
    }
}
