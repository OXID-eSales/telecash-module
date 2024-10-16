<?php

namespace OxidSolutionCatalysts\TeleCash\IPG;

use OxidSolutionCatalysts\TeleCash\IPG\API\Model;
use OxidSolutionCatalysts\TeleCash\IPG\API\Request;
use OxidSolutionCatalysts\TeleCash\IPG\API\Response;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;

/**
 * Class TeleCash
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TeleCash
{
    private string $serviceUrl;

    private string $apiUser;
    private string $apiPass;

    private string $clientCertPath;
    private string $clientKeyPath;
    private string $clientKeyPassPhrase;

    private string $serverCert;

    private OrderService|null $myService = null;

    private bool $debug = false;

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
        $this->serviceUrl          = $serviceUrl;
        $this->apiUser             = $apiUser;
        $this->apiPass             = $apiPass;
        $this->clientCertPath      = $clientCert;
        $this->clientKeyPath       = $clientKey;
        $this->clientKeyPassPhrase = $clientKeyPassPhrase;
        $this->serverCert          = $serverCert;
    }

    /**
     * Set debug mode
     *
     * @param bool $debug
     */
    public function setDebugMode(bool $debug): void
    {
        $this->debug = $debug;
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
     * Install a recurring payment.
     *
     * @param string    $hostedDataId
     * @param float     $amount
     * @param \DateTime $startDate
     * @param int       $count
     * @param int       $frequency
     * @param string    $period
     *
     * @return Response\Action\ConfirmRecurring|Response\Order\Sell|Response\Error
     * @throws \Exception
     */
    public function installRecurringPayment(
        string $hostedDataId,
        float $amount,
        \DateTime $startDate,
        int $count,
        int $frequency,
        string $period
    ): Response\Action\ConfirmRecurring|Response\Order\Sell|Response\Error {
        $service = $this->getService();

        $paymentInformation     = new Model\RecurringPaymentInformation(
            $startDate,
            $count,
            $frequency,
            $period
        );
        $payment                = new Model\Payment($hostedDataId, $amount);
        $recurringPaymentAction = new Request\Action\RecurringPayment\Install(
            $service,
            $payment,
            $paymentInformation
        );

        return $recurringPaymentAction->install();
    }

    /**
     * Install a recurring payment, which will only result in a single immediate payment.
     *
     * This is a work around for sth.
     *
     * @param string $hostedDataId
     * @param float  $amount
     *
     * @return Response\Action\ConfirmRecurring|Response\Order\Sell|Response\Error
     * @throws \Exception
     */
    public function installOneTimeRecurringPayment(
        string $hostedDataId,
        float $amount
    ): Response\Action\ConfirmRecurring|Response\Order\Sell|Response\Error {
        return $this->installRecurringPayment(
            $hostedDataId,
            $amount,
            new \DateTime(),
            1,
            1,
            Model\RecurringPaymentInformation::PERIOD_MONTH
        );
    }

    /**
     * Modify a recurring payment
     *
     * @param string         $orderId
     * @param string         $hostedDataId
     * @param float          $amount
     * @param \DateTime|null $startDate
     * @param int            $count
     * @param int            $frequency
     * @param string         $period
     *
     * @return Response\Action\ConfirmRecurring|Response\Order\Sell|Response\Error
     * @throws \Exception
     */
    public function modifyRecurringPayment(
        string $orderId,
        string $hostedDataId,
        float $amount,
        \DateTime|null $startDate,
        int $count,
        int $frequency,
        string $period
    ): Response\Action\ConfirmRecurring|Response\Order\Sell|Response\Error {
        $service = $this->getService();

        $paymentInformation     = new Model\RecurringPaymentInformation(
            $startDate,
            $count,
            $frequency,
            $period
        );
        $payment                = new Model\Payment($hostedDataId, $amount);
        $recurringPaymentAction = new Request\Action\RecurringPayment\Modify(
            $service,
            $orderId,
            $payment,
            $paymentInformation
        );

        return $recurringPaymentAction->modify();
    }

    /**
     * Cancel a recurring payment
     *
     * @param string $orderId
     *
     * @return Response\Action\ConfirmRecurring|Response\Order\Sell|Response\Error
     * @throws \Exception
     */
    public function cancelRecurringPayment(
        string $orderId
    ): Response\Action\ConfirmRecurring|Response\Order\Sell|Response\Error {
        $service = $this->getService();

        $recurringPaymentAction = new Request\Action\RecurringPayment\Cancel($service, $orderId);

        return $recurringPaymentAction->cancel();
    }


    /**
     * Get a handle to the OrderService
     *
     * @return OrderService
     */
    private function getService(): OrderService
    {
        if ($this->myService === null) {
            $curlOptions = [
                'url'          => $this->serviceUrl,
                'sslCert'      => $this->clientCertPath,
                'sslKey'       => $this->clientKeyPath,
                'sslKeyPasswd' => $this->clientKeyPassPhrase,
                'caInfo'       => $this->serverCert
            ];
            $this->myService = new OrderService(
                $curlOptions,
                $this->apiUser,
                $this->apiPass,
                $this->debug
            );
        }

        return $this->myService;
    }

    public function sendEMailNotification(
        string $orderId,
        string $tDate,
        string|null $email = null
    ): Response\Action\Validation|Response\Error {
        $service = $this->getService();
        $emailNotificationAction = new Request\Action\TriggerEmailNotification($service, $orderId, $tDate, $email);

        return $emailNotificationAction->send();
    }
}
