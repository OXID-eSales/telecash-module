<?php

namespace OxidSolutionCatalysts\TeleCash\IPG;

use OxidSolutionCatalysts\TeleCash\IPG\API\Model\BillingData;
use OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService;

class TeleCashBase
{
    protected string $serviceUrl;

    protected string $apiUser;
    protected string $apiPass;

    protected string $clientCertPath;
    protected string $clientKeyPath;
    protected string $clientKeyPassPhrase;

    protected string $serverCert;

    protected OrderService|null $myService = null;
    protected BillingData|null $billingData = null;

    protected bool $debug = false;

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
     * Set the billing data
     *
     * @param BillingData $billingData
     */
    public function setBillingData(BillingData $billingData): void
    {
        $this->billingData = $billingData;
    }

    /**
     * Get the billing data
     */
    public function getBillingData(): BillingData|null
    {
        return $this->billingData;
    }

    /**
     * Get a handle to the OrderService
     *
     * @return OrderService
     */
    protected function getService(): OrderService
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
}
