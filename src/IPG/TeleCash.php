<?php

namespace OxidSolutionCatalysts\TeleCash\IPG;

use DOMException;
use Exception;
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
class TeleCash extends TeleCashBase
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
     * @throws Exception
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
     * @throws Exception
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
     * @throws Exception
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
     * @throws Exception
     */
    public function cancelRecurringPayment(
        string $orderId
    ): Response\Action\ConfirmRecurring|Response\Order\Sell|Response\Error {
        $service = $this->getService();

        $recurringPaymentAction = new Request\Action\RecurringPayment\Cancel($service, $orderId);

        return $recurringPaymentAction->cancel();
    }

    /**
     * @throws DOMException
     * @throws Exception
     */
    public function sendEMailNotification(
        string $orderId,
        string $tDate,
        string|null $email = null
    ): Response\Action\Validation|Response\Error {
        $service = $this->getService();
        $emailNotificationAction = new Request\Action\TriggerEmailNotification($service, $orderId, $tDate, $email);

        return $emailNotificationAction->send();
    }

    /**
     * @throws DOMException
     * @throws Exception
     */
    public function postAuthOrder(
        string $orderId,
        string $currency,
        string $chargeTotal
    ): Response\Order\Sell|Response\Error {
        $service = $this->getService();
        $postAuthOrder = new Request\Action\PostAuthOrder($service, $orderId, $currency, $chargeTotal);

        return $postAuthOrder->postAuth();
    }

    /**
     * @throws DOMException
     * @throws Exception
     */
    public function getLastTransactions(
        int $count,
        string|null $orderId = null,
        string|null $tDate = null
    ): Response\Action\Validation|Response\Error {
        $service = $this->getService();
        $lastTransactionsAction = new Request\Action\LastTransactions($service, $count, $orderId, $tDate);

        return $lastTransactionsAction->get();
    }

    /**
     * @throws DOMException
     * @throws Exception
     */
    public function getLastOrders(
        int $count,
        string|null $orderId = null,
        string|null $dtFrom = null,
        string|null $dtTo = null
    ): Response\Action\Validation|Response\Error {
        $service = $this->getService();
        $lastTransactionsAction = new Request\Action\LastOrders($service, $count, $orderId, $dtFrom, $dtTo);

        return $lastTransactionsAction->get();
    }

    /**
     * @throws DOMException
     */
    public function getInquiryByIPGTransactionId(string $ipgTransactionId): Response\Action\Validation|Response\Error
    {
        $service = $this->getService();
        return (new Request\Action\InquiryTransaction($service))->getByIPGTransactionId($ipgTransactionId);
    }

    /**
     * @throws DOMException
     */
    public function getInquiryByOrderIdAndTDate(
        string $orderId,
        string $tDate
    ): Response\Action\Validation|Response\Error {
        $service = $this->getService();
        return (new Request\Action\InquiryTransaction($service))->getByOrderIdAndTDate($orderId, $tDate);
    }
}
