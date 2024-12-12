<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;

use Exception;
use OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action\Validation;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Error;
use RuntimeException;

class TriggerEmailNotification extends Action
{
    public function __construct(
        OrderService $service,
        private readonly string $orderId,
        private readonly string $tDate,
        private readonly ?string $email = null
    ) {
        parent::__construct($service);

        try {
            // Get Action element from parent
            $actionElement = $this->element->getElementsByTagName('ns2:Action')->item(0);
            if (!$actionElement) {
                throw new RuntimeException('Action element not found');
            }

            // Create notification element
            $notificationElement = $this->document->createElement('ns2:SendEMailNotification');

            // Add required elements
            $orderIdElement = $this->document->createElement('ns2:OrderId');
            $orderIdElement->textContent = $this->orderId;
            $notificationElement->appendChild($orderIdElement);

            $tdateElement = $this->document->createElement('ns2:TDate');
            $tdateElement->textContent = $this->tDate;
            $notificationElement->appendChild($tdateElement);

            // Add optional email element
            if ($this->email !== null) {
                $emailElement = $this->document->createElement('ns2:Email');
                $emailElement->textContent = $this->email;
                $notificationElement->appendChild($emailElement);
            }

            // Append to Action element
            $actionElement->appendChild($notificationElement);
        } catch (Exception $e) {
            throw new RuntimeException('Failed to create TriggerEmailNotification XML: ' . $e->getMessage());
        }
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
