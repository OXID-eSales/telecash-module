<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Response\Order;

use OxidSolutionCatalysts\TeleCash\IPG\API\AbstractResponse;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;

/**
 * Class Sell
 */
class Sell extends AbstractResponse
{

    const RESPONSE_SUCCESS = 'Function performed error-free';

    const TRANSACTION_RESULT_APPROVED       = 'APPROVED';
    const TRANSACTION_RESULT_DECLINED       = 'DECLINED';
    const TRANSACTION_RESULT_FRAUD          = 'FRAUD';
    const TRANSACTION_RESULT_FAILED         = 'FAILED';
    const TRANSACTION_RESULT_NOT_SUCCESSFUL = 'NOT_SUCCESSFUL';

    /** @var bool */
    protected bool $wasSuccessful;
    /** @var string  */
    protected string $approvalCode;
    /** @var string  */
    protected string $avsResponse;
    /** @var string  */
    protected string $brand;
    /** @var string  */
    protected string $orderId;
    /** @var string  */
    protected string $paymentType;
    /** @var string  */
    protected string $processorApprovalCode;
    /** @var string  */
    protected string $processorReceiptNumber;
    /** @var string  */
    protected string $processorReferenceNumber;
    /** @var string  */
    protected string $processorResponse;
    /** @var string  */
    protected string $processorResponseCode;
    /** @var string  */
    protected string $processorTraceNumber;
    /** @var string  */
    protected string $provider;
    /** @var string  */
    protected string $tDate;
    /** @var string  */
    protected string $terminalId;
    /** @var string  */
    protected string $transactionResult;
    /** @var string  */
    protected string $transactionTime;

    /**
     * @return string
     */
    public function getApprovalCode(): string
    {
        return $this->approvalCode;
    }

    /**
     * @return string
     */
    public function getAvsResponse(): string
    {
        return $this->avsResponse;
    }

    /**
     * @return string
     */
    public function getBrand(): string
    {
        return $this->brand;
    }

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * @return string
     */
    public function getPaymentType(): string
    {
        return $this->paymentType;
    }

    /**
     * @return string
     */
    public function getProcessorApprovalCode(): string
    {
        return $this->processorApprovalCode;
    }

    /**
     * @return string
     */
    public function getProcessorReceiptNumber(): string
    {
        return $this->processorReceiptNumber;
    }

    /**
     * @return string
     */
    public function getProcessorReferenceNumber(): string
    {
        return $this->processorReferenceNumber;
    }

    /**
     * @return string
     */
    public function getProcessorResponse(): string
    {
        return $this->processorResponse;
    }

    /**
     * @return string
     */
    public function getProcessorResponseCode(): string
    {
        return $this->processorResponseCode;
    }

    /**
     * @return string
     */
    public function getProcessorTraceNumber(): string
    {
        return $this->processorTraceNumber;
    }

    /**
     * @return string
     */
    public function getProvider(): string
    {
        return $this->provider;
    }

    /**
     * @return string
     */
    public function getTerminalId(): string
    {
        return $this->terminalId;
    }

    /**
     * @return string
     */
    public function getTDate(): string
    {
        return $this->tDate;
    }

    /**
     * @return string
     */
    public function getTransactionResult(): string
    {
        return $this->transactionResult;
    }

    /**
     * @return string
     */
    public function getTransactionTime(): string
    {
        return $this->transactionTime;
    }

    /**
     * @return bool
     */
    public function wasSuccessful(): bool
    {
        return $this->wasSuccessful;
    }

    /**
     * Checks whether the sell was successful.
     *
     * @param \DOMDocument $responseDoc
     *
     * @return bool
     */
    private function checkIfSuccessful(\DOMDocument $responseDoc): bool
    {
        $this->wasSuccessful = false;

        $list = $responseDoc->getElementsByTagNameNS(OrderService::NAMESPACE_N3, 'successfully');
        if ($list->length > 0) {
            $success = $responseDoc->getElementsByTagNameNS(OrderService::NAMESPACE_N3, 'successfully')->item(0)->nodeValue;

            $this->wasSuccessful = ($success === 'true');
        } else {
            $list = $responseDoc->getElementsByTagNameNS(OrderService::NAMESPACE_N3, 'ProcessorResponseMessage');
            if ($list->length > 0) {
                $this->wasSuccessful = ($list->item(0)->nodeValue === Sell::RESPONSE_SUCCESS);
            }

            if ($this->wasSuccessful === false) {
                $list = $responseDoc->getElementsByTagNameNS(OrderService::NAMESPACE_N3, 'TransactionResult');
                if ($list->length > 0) {
                    $this->wasSuccessful = ($list->item(0)->nodeValue === Sell::TRANSACTION_RESULT_APPROVED);
                }
            }
        }

        return $this->wasSuccessful;
    }

    /**
     * @param \DOMDocument $responseDoc
     *
     * @throws \Exception
     */
    public function __construct(\DOMDocument $responseDoc)
    {
        if ($this->checkIfSuccessful($responseDoc)) {
            $this->approvalCode             = $this->firstElementByTagNSString($responseDoc, OrderService::NAMESPACE_N3, 'ApprovalCode');
            $this->avsResponse              = $this->firstElementByTagNSString($responseDoc, OrderService::NAMESPACE_N3, 'AVSResponse');
            $this->brand                    = $this->firstElementByTagNSString($responseDoc, OrderService::NAMESPACE_N3, 'Brand');
            $this->orderId                  = $this->firstElementByTagNSString($responseDoc, OrderService::NAMESPACE_N3, 'OrderId');
            $this->paymentType              = $this->firstElementByTagNSString($responseDoc, OrderService::NAMESPACE_N3, 'PaymentType');
            $this->processorApprovalCode    = $this->firstElementByTagNSString($responseDoc, OrderService::NAMESPACE_N3, 'ProcessorApprovalCode');
            $this->processorReceiptNumber   = $this->firstElementByTagNSString($responseDoc, OrderService::NAMESPACE_N3, 'ProcessorReceiptNumber');
            $this->processorReferenceNumber = $this->firstElementByTagNSString($responseDoc, OrderService::NAMESPACE_N3, 'ProcessorReferenceNumber');
            $this->processorResponse        = $this->firstElementByTagNSString($responseDoc, OrderService::NAMESPACE_N3, 'ProcessorResponseMessage');
            $this->processorResponseCode    = $this->firstElementByTagNSString($responseDoc, OrderService::NAMESPACE_N3, 'ProcessorResponseCode');
            $this->processorTraceNumber     = $this->firstElementByTagNSString($responseDoc, OrderService::NAMESPACE_N3, 'ProcessorTraceNumber');
            $this->provider                 = $this->firstElementByTagNSString($responseDoc, OrderService::NAMESPACE_N3, 'CommercialServiceProvider');
            $this->tDate                    = $this->firstElementByTagNSString($responseDoc, OrderService::NAMESPACE_N3, 'TDate');
            $this->terminalId               = $this->firstElementByTagNSString($responseDoc, OrderService::NAMESPACE_N3, 'TerminalID');
            $this->transactionTime          = $this->firstElementByTagNSString($responseDoc, OrderService::NAMESPACE_N3, 'TransactionTime');
            $this->transactionResult        = $this->firstElementByTagNSString($responseDoc, OrderService::NAMESPACE_N3, 'TransactionResult');
        } else {
            $this->transactionResult     = Sell::TRANSACTION_RESULT_NOT_SUCCESSFUL;
            $this->processorResponseCode = $responseDoc->getElementsByTagNameNS(OrderService::NAMESPACE_N2, 'Error')->item(0)->attributes->getNamedItem('Code')->nodeValue;
            $this->processorResponse     = $responseDoc->getElementsByTagNameNS(OrderService::NAMESPACE_N2, 'ErrorMessage')->item(0)->nodeValue;
        }
    }
}
