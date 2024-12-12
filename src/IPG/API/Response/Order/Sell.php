<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Response\Order;

use DOMDocument;
use Exception;
use OxidSolutionCatalysts\TeleCash\IPG\API\AbstractResponse;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashConstants;

/**
 * Class Sell
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Sell extends AbstractResponse
{
    public const RESPONSE_SUCCESS = 'Function performed error-free';

    public const TRANSACTION_RESULT_APPROVED       = 'APPROVED';
    public const TRANSACTION_RESULT_DECLINED       = 'DECLINED';
    public const TRANSACTION_RESULT_FRAUD          = 'FRAUD';
    public const TRANSACTION_RESULT_FAILED         = 'FAILED';
    public const TRANSACTION_RESULT_NOT_SUCCESSFUL = 'NOT_SUCCESSFUL';

    /** @var bool */
    protected bool $wasSuccessful;
    /** @var string|null  */
    protected string|null $approvalCode;
    /** @var string|null  */
    protected string|null $avsResponse;
    /** @var string|null  */
    protected string|null $brand;
    /** @var string|null  */
    protected string|null $orderId;
    /** @var string|null  */
    protected string|null $paymentType;
    /** @var string|null  */
    protected string|null $processorApprovalCode;
    /** @var string|null  */
    protected string|null $processorReceiptNumber;
    /** @var string|null  */
    protected string|null $processorReferenceNumber;
    /** @var string|null  */
    protected string|null $processorResponse;
    /** @var string|null  */
    protected string|null $processorResponseCode;
    /** @var string|null  */
    protected string|null $processorTraceNumber;
    /** @var string|null  */
    protected string|null $provider;
    /** @var string|null  */
    protected string|null $tDate;
    /** @var string|null  */
    protected string|null $terminalId;
    /** @var string|null  */
    protected string|null $transactionResult;
    /** @var string|null  */
    protected string|null $transactionTime;

    /**
     * @return string|null
     */
    public function getApprovalCode(): string|null
    {
        return $this->approvalCode;
    }

    /**
     * @return string|null
     */
    public function getAvsResponse(): string|null
    {
        return $this->avsResponse;
    }

    /**
     * @return string|null
     */
    public function getBrand(): string|null
    {
        return $this->brand;
    }

    /**
     * @return string|null
     */
    public function getOrderId(): string|null
    {
        return $this->orderId;
    }

    /**
     * @return string|null
     */
    public function getPaymentType(): string|null
    {
        return $this->paymentType;
    }

    /**
     * @return string|null
     */
    public function getProcessorApprovalCode(): string|null
    {
        return $this->processorApprovalCode;
    }

    /**
     * @return string|null
     */
    public function getProcessorReceiptNumber(): string|null
    {
        return $this->processorReceiptNumber;
    }

    /**
     * @return string|null
     */
    public function getProcessorReferenceNumber(): string|null
    {
        return $this->processorReferenceNumber;
    }

    /**
     * @return string|null
     */
    public function getProcessorResponse(): string|null
    {
        return $this->processorResponse;
    }

    /**
     * @return string|null
     */
    public function getProcessorResponseCode(): string|null
    {
        return $this->processorResponseCode;
    }

    /**
     * @return string|null
     */
    public function getProcessorTraceNumber(): string|null
    {
        return $this->processorTraceNumber;
    }

    /**
     * @return string|null
     */
    public function getProvider(): string|null
    {
        return $this->provider;
    }

    /**
     * @return string|null
     */
    public function getTerminalId(): string|null
    {
        return $this->terminalId;
    }

    /**
     * @return string|null
     */
    public function getTDate(): string|null
    {
        return $this->tDate;
    }

    /**
     * @return string|null
     */
    public function getTransactionResult(): string|null
    {
        return $this->transactionResult;
    }

    /**
     * @return string|null
     */
    public function getTransactionTime(): string|null
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
     * @param DOMDocument $responseDoc
     *
     * @return bool
     */
    private function checkIfSuccessful(DOMDocument $responseDoc): bool
    {
        $this->wasSuccessful = false;

        $list = $responseDoc->getElementsByTagNameNS(TeleCashConstants::NAMESPACE_IPGAPI, 'successfully');
        if ($list->length > 0) {
            $success = $responseDoc->getElementsByTagNameNS(
                TeleCashConstants::NAMESPACE_IPGAPI,
                'successfully'
            )->item(0);
            if ($success) {
                $success = $success->nodeValue;
            }


            $this->wasSuccessful = ($success === 'true');
        } else {
            $list = $responseDoc->getElementsByTagNameNS(
                TeleCashConstants::NAMESPACE_IPGAPI,
                'ProcessorResponseMessage'
            );
            if ($list->length > 0) {
                $item0 = $list->item(0);
                $this->wasSuccessful = false;
                if ($item0) {
                    $this->wasSuccessful = ($item0->nodeValue === Sell::RESPONSE_SUCCESS);
                }
            }

            if ($this->wasSuccessful === false) {
                $list = $responseDoc->getElementsByTagNameNS(
                    TeleCashConstants::NAMESPACE_IPGAPI,
                    'TransactionResult'
                );
                if ($list->length > 0) {
                    $item0 = $list->item(0);
                    $this->wasSuccessful = false;
                    if ($item0) {
                        $this->wasSuccessful = ($item0->nodeValue === Sell::TRANSACTION_RESULT_APPROVED);
                    }
                }
            }
        }

        return $this->wasSuccessful;
    }

    /**
     * @param DOMDocument $responseDoc
     *
     * @throws Exception
     */
    public function __construct(DOMDocument $responseDoc)
    {
        if ($this->checkIfSuccessful($responseDoc)) {
            $this->initSelf($responseDoc);
        } else {
            $this->transactionResult     = Sell::TRANSACTION_RESULT_NOT_SUCCESSFUL;
            $item0 = $responseDoc->getElementsByTagNameNS(
                TeleCashConstants::NAMESPACE_A1,
                'Error'
            )->item(0);
            if ($item0) {
                $item1 = $item0->attributes->getNamedItem('Code');
                if ($item1) {
                    $this->processorResponseCode = $item1->nodeValue;
                }
            }
            unset($item0);

            $item0 = $responseDoc->getElementsByTagNameNS(
                TeleCashConstants::NAMESPACE_A1,
                'ErrorMessage'
            )->item(0);
            if ($item0) {
                $this->processorResponse = $item0->nodeValue;
            }
        }
    }

    /**
     * @throws Exception
     */
    private function initSelf(DOMDocument $responseDoc): void
    {
        $this->approvalCode             = $this->firstElementByTagNSString(
            $responseDoc,
            TeleCashConstants::NAMESPACE_IPGAPI,
            'ApprovalCode'
        );
        $this->avsResponse              = $this->firstElementByTagNSString(
            $responseDoc,
            TeleCashConstants::NAMESPACE_IPGAPI,
            'AVSResponse'
        );
        $this->brand                    = $this->firstElementByTagNSString(
            $responseDoc,
            TeleCashConstants::NAMESPACE_IPGAPI,
            'Brand',
            true,
            ''
        );
        $this->orderId                  = $this->firstElementByTagNSString(
            $responseDoc,
            TeleCashConstants::NAMESPACE_IPGAPI,
            'OrderId'
        );
        $this->paymentType              = $this->firstElementByTagNSString(
            $responseDoc,
            TeleCashConstants::NAMESPACE_IPGAPI,
            'PaymentType'
        );
        $this->processorApprovalCode    = $this->firstElementByTagNSString(
            $responseDoc,
            TeleCashConstants::NAMESPACE_IPGAPI,
            'ProcessorApprovalCode'
        );
        $this->processorReceiptNumber   = $this->firstElementByTagNSString(
            $responseDoc,
            TeleCashConstants::NAMESPACE_IPGAPI,
            'ProcessorReceiptNumber'
        );
        $this->processorReferenceNumber = $this->firstElementByTagNSString(
            $responseDoc,
            TeleCashConstants::NAMESPACE_IPGAPI,
            'ProcessorReferenceNumber'
        );
        $this->processorResponse        = $this->firstElementByTagNSString(
            $responseDoc,
            TeleCashConstants::NAMESPACE_IPGAPI,
            'ProcessorResponseMessage'
        );
        $this->processorResponseCode    = $this->firstElementByTagNSString(
            $responseDoc,
            TeleCashConstants::NAMESPACE_IPGAPI,
            'ProcessorResponseCode'
        );
        $this->processorTraceNumber     = $this->firstElementByTagNSString(
            $responseDoc,
            TeleCashConstants::NAMESPACE_IPGAPI,
            'ProcessorTraceNumber'
        );
        $this->provider                 = $this->firstElementByTagNSString(
            $responseDoc,
            TeleCashConstants::NAMESPACE_IPGAPI,
            'CommercialServiceProvider'
        );
        $this->tDate                    = $this->firstElementByTagNSString(
            $responseDoc,
            TeleCashConstants::NAMESPACE_IPGAPI,
            'TDate'
        );
        $this->terminalId               = $this->firstElementByTagNSString(
            $responseDoc,
            TeleCashConstants::NAMESPACE_IPGAPI,
            'TerminalID'
        );
        $this->transactionTime          = $this->firstElementByTagNSString(
            $responseDoc,
            TeleCashConstants::NAMESPACE_IPGAPI,
            'TransactionTime'
        );
        $this->transactionResult        = $this->firstElementByTagNSString(
            $responseDoc,
            TeleCashConstants::NAMESPACE_IPGAPI,
            'TransactionResult'
        );
    }
}
