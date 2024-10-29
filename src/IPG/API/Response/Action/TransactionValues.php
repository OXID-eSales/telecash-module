<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action;

use OxidSolutionCatalysts\TeleCash\IPG\API\AbstractResponse;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;

class TransactionValues extends AbstractResponse
{
    /**
     * @var array<string, mixed> $fieldMap
     */
    protected array $fieldMap = [
        OrderService::NAMESPACE_N1 => [
            'Type',
            'CardNumber', 'ExpMonth', 'ExpYear', 'Brand',
            'ChargeTotal', 'Currency',
            'Comments', 'OrderId', 'TDate',
        ],
        OrderService::NAMESPACE_N2 => [
            'ReceiptNumber', 'TraceNumber', 'Brand', 'TransactionType', 'TransactionState', 'UserID',
            'SubmissionComponent'
        ],
        OrderService::NAMESPACE_N3 => [
            'ApprovalCode', 'AVSResponse', 'Brand', 'Country', 'OrderId', 'IpgTransactionId', 'PaymentType',
            'ProcessorApprovalCode', 'ProcessorReceiptNumber', 'ProcessorCCVResponse', 'ProcessorTraceNumber',
            'ReferencedTDate', 'SchemeTransactionId', 'TDate', 'TDateFormatted', 'TerminalID',
        ],
    ];

    /**
     * @var array<string, mixed> $valueMap
     */
    protected array $valueMap = [];

    public function __construct(\DOMDocument $responseDoc)
    {
        foreach ($this->fieldMap as $namespace => $fields) {
            foreach ($fields as $field) {
                $fieldValue = $this->firstElementByTagNSString(
                    $responseDoc,
                    $namespace,
                    $field
                );

                $this->valueMap[ $namespace ][ $field ] = $fieldValue;
            }
        }

        print_r($this->valueMap);
    }
}
