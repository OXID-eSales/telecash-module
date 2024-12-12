<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Core\Service;

use DOMException;
use Exception;
use OxidSolutionCatalysts\TeleCash\Application\Model\TeleCashOrderHistory;
use OxidSolutionCatalysts\TeleCash\Core\Module;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Error;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response\Order\Sell;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashDateTime;
use OxidSolutionCatalysts\TeleCash\Application\Model\TeleCashOrder;
use OxidSolutionCatalysts\TeleCash\Exception\TeleCashException;

/**
 * Service for handling TeleCash order processing after initial transaction.
 * Provides functionality for charging orders and handling the responses.
 */
class TeleCashAfterProcessService implements TeleCashAfterProcessServiceInterface
{
    private TeleCashAPIServiceInterface $apiService;
    private OxNewService $oxNewService;
    private ErrorDisplayServiceInterface $errorDisplay;

    public function __construct(
        TeleCashAPIServiceInterface $apiService,
        OxNewService $oxNewService,
        ErrorDisplayServiceInterface $errorDisplay
    ) {
        $this->apiService = $apiService;
        $this->oxNewService = $oxNewService;
        $this->errorDisplay = $errorDisplay;
    }

    /**
     * Processes a charge for a TeleCash order.
     * Loads the order, executes the charge via API and handles the response.
     *
     * @param string $orderId The ID of the order to charge
     * @param float $amount The amount to charge
     * @throws DOMException If there's an XML processing error
     * @throws TeleCashException If the order cannot be loaded
     * @throws Exception For general errors during processing
     */
    public function doChargeOrder(string $orderId, float $amount): void
    {
        $teleCashOrder = $this->oxNewService->oxNew(TeleCashOrder::class);
        if (!$teleCashOrder->load($orderId)) {
            throw new TeleCashException('Could not load TeleCash order');
        }

        $amountString = (string)$amount;
        $currency = $teleCashOrder->getCurrency();
        $teleCashApi = $this->apiService->getTeleCashAPI();

        $result = $teleCashApi->postAuthOrder(
            $teleCashOrder->getOid(),
            $currency,
            $amountString
        );

        if ($result instanceof Sell) {
            $this->handleSuccessfulCharge($result, $teleCashOrder, $currency, $amountString);
        }

        if ($result instanceof Error) {
            $this->errorDisplay->showErrorMessage($result->getClientErrorDetail());
        }
    }

    /**
     * Handles a successful charge response.
     * Creates and saves a transaction history entry with the response details.
     *
     * @param Sell $result The successful API response
     * @param TeleCashOrder $teleCashOrder The order being processed
     * @param string $currency The currency used for the transaction
     * @param string $amountString The charged amount as string
     * @throws Exception
     */
    private function handleSuccessfulCharge(
        Sell $result,
        TeleCashOrder $teleCashOrder,
        string $currency,
        string $amountString
    ): void {
        $teleCashDateTime = new TeleCashDateTime();
        $txnDateTime = $teleCashDateTime->getFormatDateTimeFromTimeStamp((int)$result->getTransactionTime());

        $orderHistory = $this->oxNewService->oxNew(TeleCashOrderHistory::class);
        $orderHistory->setTransactionResult([
            'oid'                     => (string) $result->getOrderId(),
            'txntype'                 => Module::TELECASH_TXN_TYPE_POSTAUTH,
            'txndatetime'             => $txnDateTime,
            'terminal_id'             => (string) $result->getTerminalId(),
            'currency'                => $currency,
            'chargetotal'             => $amountString,
            'status'                  => (string) $result->getTransactionResult(),
            'processor_response_code' => (string) $result->getProcessorResponseCode(),
            'paymentMethod'           => $teleCashOrder->getPaymentMethod()
        ]);
        $orderHistory->save();
    }
}
