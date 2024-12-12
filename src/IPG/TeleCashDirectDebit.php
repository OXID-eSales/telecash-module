<?php

namespace OxidSolutionCatalysts\TeleCash\IPG;

use OxidSolutionCatalysts\TeleCash\Core\Service\Logger;
use OxidSolutionCatalysts\TeleCash\IPG\API\Model;
use OxidSolutionCatalysts\TeleCash\IPG\API\Request;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;

class TeleCashDirectDebit extends TeleCashBase
{
    /**
     * Constructor
     *
     * @param string $serviceUrl
     * @param string $apiUser
     * @param string $apiPass
     * @param string $clientCert
     * @param string $clientKey
     * @param string $clientKeyPassPhrase
     * @param string $serverCert
     * @param Logger $logger
     */
    public function __construct(
        string $serviceUrl,
        string $apiUser,
        string $apiPass,
        string $clientCert,
        string $clientKey,
        string $clientKeyPassPhrase,
        string $serverCert,
        private readonly Logger $logger
    ) {
        parent::__construct(
            $serviceUrl,
            $apiUser,
            $apiPass,
            $clientCert,
            $clientKey,
            $clientKeyPassPhrase,
            $serverCert,
            $this->logger
        );
    }

    /**
     * Make a sale using direct credit card information
     *
     * @param string      $bankCode
     * @param string      $accountNumber
     * @param float       $amount
     * @param string|null $comments
     * @param string|null $invoiceNumber
     *
     * @return Response\Order\Sell|Response\Error
     * @throws \Exception
     */
    public function sellWithBankAccount(
        string $bankCode,
        string $accountNumber,
        float $amount,
        string|null $comments = null,
        string|null $invoiceNumber = null
    ): Response\Order\Sell|Response\Error {
        $service = $this->getService();

        $ddData     = new Model\DirectDebitData(null, $bankCode, $accountNumber);
        $payment    = new Model\Payment(null, $amount);
        if (!empty($comments) || !empty($invoiceNumber)) {
            $transactionDetails = new Model\TransactionDetails(
                'ns1',
                $comments,
                $invoiceNumber
            );
        } else {
            $transactionDetails = null;
        }
        $sellAction = new Request\Transaction\SellDirectDebit(
            $service,
            $ddData,
            $payment,
            $this->getBillingData(),
            $transactionDetails
        );

        return $sellAction->sell();
    }

    /**
     * Make a sale using direct credit card information
     *
     * @param string      $iBAN
     * @param float       $amount
     * @param string|null $comments
     * @param string|null $invoiceNumber
     *
     * @return Response\Order\Sell|Response\Error
     * @throws \Exception
     */
    public function sellWithIBAN(
        string $iBAN,
        float $amount,
        string|null $comments = null,
        string|null $invoiceNumber = null
    ): Response\Order\Sell|Response\Error {
        $service = $this->getService();

        $ddData     = new Model\DirectDebitData($iBAN, null, null);
        $payment    = new Model\Payment(null, $amount);
        if (!empty($comments) || !empty($invoiceNumber)) {
            $transactionDetails = new Model\TransactionDetails(
                'ns1',
                $comments,
                $invoiceNumber
            );
        } else {
            $transactionDetails = null;
        }
        $sellAction = new Request\Transaction\SellDirectDebit(
            $service,
            $ddData,
            $payment,
            $this->billingData,
            $transactionDetails
        );

        return $sellAction->sell();
    }
}
