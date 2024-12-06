<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;

use Exception;
use OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action\Validation;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Error;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashConstants;

class TriggerEmailNotification extends Action
{
    public function __construct(OrderService $service, string $orderId, string $tDate, string|null $email = null)
    {
        parent::__construct($service);

        $xml = $this->document->createElement(TeleCashConstants::PREF_A1 . 'SendEMailNotification');
        $xml->appendChild($this->document->createElement(TeleCashConstants::PREF_A1 . 'OrderId', $orderId));
        $xml->appendChild($this->document->createElement(TeleCashConstants::PREF_A1 . 'TDate', $tDate));
        if ($email) {
            $xml->appendChild($this->document->createElement(TeleCashConstants::PREF_A1 . 'Email', $email));
        }
        $item0 = $this->element->getElementsByTagName(TeleCashConstants::PREF_A1 . 'Action')->item(0);
        $item0?->appendChild($xml);
    }

    /**
     * @throws Exception
     */
    public function send(): Error|Validation
    {
        $response = $this->service->IPGApiAction($this);

        return $response instanceof Error ? $response : new Validation($response);
    }
}
