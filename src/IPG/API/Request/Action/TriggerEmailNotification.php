<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;

use OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action\Validation;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Error;

class TriggerEmailNotification extends Action
{
    public function __construct(OrderService $service, string $orderId, string $tDate, string|null $email = null)
    {
        parent::__construct($service);

        $xml = $this->document->createElement('ns2:SendEMailNotification');
        $xml->appendChild($this->document->createElement('ns2:OrderId', $orderId));
        $xml->appendChild($this->document->createElement('ns2:TDate', $tDate));
        if ($email) {
            $xml->appendChild($this->document->createElement('ns2:Email', $email));
        }
        $item0 = $this->element->getElementsByTagName('ns2:Action')->item(0);
        if ($item0) {
            $item0->appendChild($xml);
        }
    }

    public function send(): Error|Validation
    {
        $response = $this->service->IPGApiAction($this);

        return $response instanceof Error ? $response : new Validation($response);
    }
}
