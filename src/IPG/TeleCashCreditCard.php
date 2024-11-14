<?php

namespace OxidSolutionCatalysts\TeleCash\IPG;

use OxidSolutionCatalysts\TeleCash\IPG\API\Model;
use OxidSolutionCatalysts\TeleCash\IPG\API\Request;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;

/**
 * Class TeleCashCreditCard
 *
 * @todo Consider to reduce the number of dependencies
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TeleCashCreditCard extends TeleCashBase
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
     */
    public function __construct(
        string $serviceUrl,
        string $apiUser,
        string $apiPass,
        string $clientCert,
        string $clientKey,
        string $clientKeyPassPhrase,
        string $serverCert
    ) {
        parent::__construct(
            $serviceUrl,
            $apiUser,
            $apiPass,
            $clientCert,
            $clientKey,
            $clientKeyPassPhrase,
            $serverCert
        );
    }

    /**
     * Validate credit card information
     *
     * @param string $ccNumber
     * @param string $ccValid
     * @param float  $amount
     * @param string $text
     *
     * @return Response\Action\Validation|Response\Error
     * @throws \Exception
     */
    public function validate(
        string $ccNumber,
        string $ccValid,
        float $amount = 1.0,
        string $text = null
    ): Response\Action\Validation|Response\Error {
        $service = $this->getService();

        $validMonth     = substr($ccValid, 0, 2);
        $validYear      = substr($ccValid, 3, 4);
        $ccData         = new Model\CreditCardData($ccNumber, $validMonth, $validYear);
        $validateAction = new Request\Action\Validate($service, $ccData, $amount, $text);

        return $validateAction->validate();
    }

    /**
     * Store credit card information externally
     *
     * @param string $ccNumber
     * @param string $ccValid
     * @param string $hostedDataId
     *
     * @return Response\Action\Confirm|Response\Error
     * @throws \Exception
     */
    public function storeHostedData(
        string $ccNumber,
        string $ccValid,
        string $hostedDataId
    ): Response\Action\Confirm|Response\Error {
        $service = $this->getService();

        $validMonth  = substr($ccValid, 0, 2);
        $validYear   = substr($ccValid, 3, 4);
        $ccData      = new Model\CreditCardData($ccNumber, $validMonth, $validYear);
        $ccItem      = new Model\CreditCardItem($ccData, $hostedDataId);
        $storeAction = new Request\Action\StoreHostedData($service, $ccItem);

        return $storeAction->store();
    }

    /**
     * Display externally stored data
     *
     * @param string $hostedDataId
     *
     * @return Response\Action\Display|Response\Error
     * @throws \Exception
     */
    public function displayHostedData(string $hostedDataId): Response\Action\Display|Response\Error
    {
        $service = $this->getService();

        $storageItem   = new Model\DataStorageItem($hostedDataId);
        $displayAction = new Request\Action\DisplayHostedData($service, $storageItem);

        return $displayAction->display();
    }

    /**
     * Validate externally store data
     *
     * @param string $hostedDataId
     *
     * @return Response\Action\Validation|Response\Error
     * @throws \Exception
     */
    public function validateHostedData(string $hostedDataId): Response\Action\Validation|Response\Error
    {
        $service = $this->getService();

        $payment        = new Model\Payment($hostedDataId);
        $validateAction = new Request\Action\ValidateHostedData($service, $payment);

        return $validateAction->validate();
    }

    /**
     * Delete externally store data
     *
     * @param string $hostedDataId
     *
     * @return Response\Action\Confirm|Response\Error
     * @throws \Exception
     */
    public function deleteHostedData(string $hostedDataId): Response\Action\Confirm|Response\Error
    {
        $service = $this->getService();

        $storageItem  = new Model\DataStorageItem($hostedDataId);
        $deleteAction = new Request\Action\DeleteHostedData($service, $storageItem);

        return $deleteAction->delete();
    }

    /**
     * Make a sale using a previously stored credit card information
     *
     * @param string      $hostedDataId
     * @param float       $amount
     * @param string|null $comments
     * @param string|null $invoiceNumber
     *
     * @return Response\Order\Sell|Response\Error
     * @throws \Exception
     */
    public function sellUsingHostedData(
        string $hostedDataId,
        float $amount,
        string|null $comments = null,
        string|null $invoiceNumber = null
    ): Response\Order\Sell|Response\Error {
        $service = $this->getService();

        $payment = new Model\Payment($hostedDataId, $amount);
        if (!empty($comments) || !empty($invoiceNumber)) {
            $transactionDetails = new Model\TransactionDetails(
                'ns1',
                $comments,
                $invoiceNumber
            );
        } else {
            $transactionDetails = null;
        }
        $sellAction = new Request\Transaction\SellHostedData(
            $service,
            $payment,
            $transactionDetails
        );

        return $sellAction->sell();
    }


    /**
     * Make a sale using direct credit card information
     *
     * @param string      $ccNumber
     * @param string      $ccValid
     * @param float       $amount
     * @param string|null $comments
     * @param string|null $invoiceNumber
     *
     * @return Response\Order\Sell|Response\Error
     * @throws \Exception
     */
    public function sell(
        string $ccNumber,
        string $ccValid,
        float $amount,
        string|null $comments = null,
        string|null $invoiceNumber = null
    ): Response\Order\Sell|Response\Error {
        $service = $this->getService();

        $validMonth = substr($ccValid, 0, 2);
        $validYear  = substr($ccValid, 3, 4);
        $ccData     = new Model\CreditCardData($ccNumber, $validMonth, $validYear);
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
        $sellAction = new Request\Transaction\SellCreditCard(
            $service,
            $ccData,
            $payment,
            $transactionDetails
        );

        return $sellAction->sell();
    }
}
