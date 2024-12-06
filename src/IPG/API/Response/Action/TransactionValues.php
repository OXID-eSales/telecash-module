<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Response\Action;

use Exception;
use OxidSolutionCatalysts\TeleCash\IPG\API\AbstractResponse;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashConstants;

class TransactionValues extends AbstractResponse
{
    /**
     * @var array<string, mixed> $fieldMap
     */
    protected array $fieldMap = [
        TeleCashConstants::NAMESPACE_V1 => [
            'Type',
            'CardNumber', 'ExpMonth', 'ExpYear', 'Brand',
            'ChargeTotal', 'Currency',
            'Comments', 'OrderId', 'TDate',
        ],
        TeleCashConstants::NAMESPACE_A1 => [
            'ReceiptNumber', 'TraceNumber', 'Brand', 'TransactionType', 'TransactionState', 'UserID',
            'SubmissionComponent'
        ],
        TeleCashConstants::NAMESPACE_IPGAPI => [
            'ApprovalCode', 'AVSResponse', 'Brand', 'Country', 'OrderId', 'IpgTransactionId', 'PaymentType',
            'ProcessorApprovalCode', 'ProcessorReceiptNumber', 'ProcessorCCVResponse', 'ProcessorTraceNumber',
            'ReferencedTDate', 'SchemeTransactionId', 'TDate', 'TDateFormatted', 'TerminalID',
        ],
    ];

    /**
     * @var array<string, mixed> $valueMap
     */
    protected array $valueMap = [];

    /**
     * @throws Exception
     */
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
