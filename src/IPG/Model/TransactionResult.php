<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\IPG\Model;

/**
 * TransactionResult class for handling field-value mappings
 */
class TransactionResult extends Base
{
    /**
     * List of available TransactionResult field names
     *
     * @var array<int, string>
     */
    protected array $fields = [
        'txntype', 'txndatetime', 'txndate_processed', 'timezone', 'oid', 'tdate',
        'approval_code', 'response_hash', 'response_code_3dsecure', 'hash_algorithm',
        'processor_response_code', 'endpointTransactionId', 'terminal_id', 'transactionNotificationURL',
        'currency', 'chargetotal', 'installments_interest', 'customerid', 'refnumber',
        'paymentMethod', 'ipgTransactionId', 'status', 'fail_rc', 'fail_reason',
        'merchantTransactionId', 'storename', 'schemeTransactionId'
    ];

    /**
     * DirectDebitData constructor.
     *
     * @param array<string, string> $data Input data array to parse
     */

    public function __construct(array $data)
    {
        $this->parseFromArray($data);
    }
}
