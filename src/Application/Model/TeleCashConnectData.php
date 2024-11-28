<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Application\Model;

use DateTime;
use Doctrine\DBAL\Exception;
use OxidEsales\Eshop\Core\Price as oxPrice;
use OxidEsales\Eshop\Application\Model\Address;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Application\Model\State;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidSolutionCatalysts\TeleCash\Application\Model\Interface\TeleCashConnectDataInterface;
use OxidSolutionCatalysts\TeleCash\Core\Service\Logger;
use OxidSolutionCatalysts\TeleCash\Core\Service\OxNewService;
use OxidSolutionCatalysts\TeleCash\Core\Service\Price;
use OxidSolutionCatalysts\TeleCash\IPG\TeleCashConnect;
use OxidSolutionCatalysts\TeleCash\Traits\Json;

/**
 * Class TeleCashConnectData - Provider for TeleCashData
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TeleCashConnectData implements TeleCashConnectDataInterface
{
    use Json;

    protected ?User $user = null;

    protected ?Address $address = null;

    protected ?Basket $basket = null;

    protected string $oxidLanguage;

    protected string $transactionType;

    protected string $paymentMethod;

    protected string $responseFailURL;

    protected string $responseSuccessURL;

    protected string $transactionNotificationURL;

    /**
     * Constructor for TeleCashConnectData
     *
     * Initializes a new instance of the TeleCashPayment class. This constructor can be used
     * in both production and test environments due to its flexible parameter configuration.
     *
     * @param TeleCashConnect $teleCashConnect  The TeleCash Connector
     * @param OxNewService $oxNewService The oxNewService
     */
    public function __construct(
        private readonly TeleCashConnect $teleCashConnect,
        private readonly OxNewService $oxNewService,
        private readonly Logger $logger
    ) {
    }

    /**
     * Sets the OXID-Basket
     */
    public function setOxidBasket(Basket $basket): void
    {
        $this->basket = $basket;
    }

    /**
     * Sets additional OXID-User-Delivery-Address
     */
    public function setOxidAddress(Address $address): void
    {
        $this->address = $address;
    }

    /**
     * Sets TeleCash TransactionType e.g. sale
     */
    public function setTransactionType(string $txnType): void
    {
        $this->transactionType = $txnType;
    }

    /**
     * Sets TeleCash paymentMethod e.g. 'M'
     */
    public function setPaymentMethod(string $paymentMethod): void
    {
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * Sets OXID-Language e.g. EN
     */
    public function setOxidLanguage(string $language): void
    {
        $this->oxidLanguage = $language;
    }

    public function setFailUrl(string $responseFailURL): void
    {
        $this->responseFailURL = $responseFailURL;
    }

    /**
     * Sets TeleCash Success Url
     */
    public function setSuccessUrl(string $responseSuccessURL): void
    {
        $this->responseSuccessURL = $responseSuccessURL;
    }

    /**
     * Sets TeleCash Notification Url
     */
    public function setNotificationUrl(string $transactionNotificationURL): void
    {
        $this->transactionNotificationURL = $transactionNotificationURL;
    }

    /**
     * get TeleCash formed Data
     *
     * @return array<string, string>
     */
    public function getTeleCashConnectData(): array
    {
        $allFields = $this->teleCashConnect->mergeFormFields(
            $this->getBasicFields(),
            $this->getAdditionalFields(),
            $this->getBillingFields(),
            $this->getShippingFields(),
        );
        $extHash = $this->teleCashConnect->calculateExtendedHashFromArray($allFields);
        $allFields['hashExtended'] = $extHash;

        $this->logger->log(
            'debug',
            'Debug: getTeleCashConnectData: ' . $this->arrayToJson($allFields)
        );

        return $allFields;
    }

    /**
     * get the TeleCash formed Data as hiddenfields-html-String
     *
     * @return string
     */
    public function getTeleCashConnectDataAsHiddenFields(): string
    {
        return $this->teleCashConnect->getHiddenFormFields($this->getTeleCashConnectData());
    }

    /**
     * get Basic Data for TeleCash Connect
     *
     * @return array<string, string>
     */
    private function getBasicFields(): array
    {
        $oxidCurrency = '';
        $oxidBasketTotal = '';
        $basket = $this->basket;

        // Basket-Total
        if ($basket) {
            $oxidCurrency = $basket->getBasketCurrency();
            $basketPrice = $basket->getPrice();
            if ($basketPrice) {
                $priceService = $this->createPriceService($basketPrice, $oxidCurrency);
                $oxidBasketTotal = $priceService->getFormattedBruttoPrice();
            }
        }

        // TeleCash mapped Currency
        $teleCashCurrency = $oxidCurrency ?
            $this->teleCashConnect->getCurrencyCodeByShortname($oxidCurrency->name) :
            '';

        return [
            'timezone'                   => date_default_timezone_get(),
            'txntype'                    => $this->transactionType,
            'chargetotal'                => $oxidBasketTotal,
            'currency'                   => $teleCashCurrency,
            'txndatetime'                => $this->teleCashConnect->formatDateTime(new DateTime()),
            'responseFailURL'            => $this->responseFailURL,
            'responseSuccessURL'         => $this->responseSuccessURL,
            'transactionNotificationURL' => $this->transactionNotificationURL,
            'checkoutoption'             => 'combinedpage',
            'storename'                  => $this->teleCashConnect->getStoreName(),
            'hash_algorithm'             => $this->teleCashConnect->getHashMethod()
        ];
    }

    /**
     * get Additional Data for TeleCash Connect
     *
     * @return array<string, string>
     */
    private function getAdditionalFields(): array
    {
        /** @var \OxidSolutionCatalysts\TeleCash\Extension\Application\Model\User $user */
        $user = $this->basket?->getBasketUser();
        $customerId = $user ? $user->getFieldStringData('oxcustnr') : '';

        return [
            'language'      => $this->oxidLanguage,
            'customerid'    => $customerId,
            'paymentMethod' => $this->paymentMethod
        ];
    }

    /**
     * get Billing Data for TeleCash Connect
     *
     * @return array<string, string>
     */
    private function getBillingFields(): array
    {
        /** @var \OxidSolutionCatalysts\TeleCash\Extension\Application\Model\User $user */
        $user = $this->basket?->getBasketUser();
        if (!$user) {
            return [];
        }

        return [
            'bcompany' => $user->getFieldStringData('oxcompany'),
            'bname'    => trim(
                $user->getFieldStringData('oxfname') . ' ' . $user->getFieldStringData('oxlname')
            ),
            'baddr1'   => trim(
                $user->getFieldStringData('oxstreet') . ' ' . $user->getFieldStringData('oxstreetnr')
            ),
            'baddr2'   => $user->getFieldStringData('oxaddinfo'),
            'bcity'    => $user->getFieldStringData('oxcity'),
            'bstate'   => $this->getStateName(
                $user->getFieldStringData('oxcountryid'),
                $user->getFieldStringData('oxstateid')
            ),
            'bcountry' => $this->getCountryName($user->getFieldStringData('oxcountryid')),
            'bzip'     => $user->getFieldStringData('oxzip'),
            'phone'    => $user->getFieldStringData('oxphone'),
            'fax'      => $user->getFieldStringData('oxfax'),
            'email'    => $user->getFieldStringData('oxusername'),
        ];
    }

    /**
     * get Shipping Data for TeleCash Connect
     *
     * @return array<string, string>
     */
    private function getShippingFields(): array
    {
        /** @var \OxidSolutionCatalysts\TeleCash\Extension\Application\Model\Address $addressObj */
        $addressObj = $this->address;

        // Fallback get the Shipping-Address from Billing-Address
        if (!$addressObj) {
            /** @var \OxidSolutionCatalysts\TeleCash\Extension\Application\Model\User $addressObj */
            $addressObj = $this->basket?->getBasketUser();
            if (!$addressObj) {
                return [];
            }
        }

        // Normally, OXID also recognizes a company in the delivery address. Syntactically, it would be the
        // TeleCash field "scompany". However, TeleCash does not recognize "scompany". Providing fields unknown
        // to Telecash leads to transaction maintenance. That's why we're leaving it out here and just providing
        // information at this point.
        // 'scompany' => $addressObj->getFieldStringData('oxcompany'),

        return [
            'sname'    => trim(
                $addressObj->getFieldStringData('oxfname') . ' ' . $addressObj->getFieldStringData('oxlname')
            ),
            'saddr1'   => trim(
                $addressObj->getFieldStringData('oxstreet') . ' ' . $addressObj->getFieldStringData('oxstreetnr')
            ),
            'saddr2'   => $addressObj->getFieldStringData('oxaddinfo'),
            'scity'    => $addressObj->getFieldStringData('oxcity'),
            'sstate'   => $this->getStateName(
                $addressObj->getFieldStringData('oxcountryid'),
                $addressObj->getFieldStringData('oxstateid')
            ),
            'scountry' => $this->getCountryName($addressObj->getFieldStringData('oxcountryid')),
            'szip'     => $addressObj->getFieldStringData('oxzip'),
        ];
    }

    /**
     * get State Name from OXID CountryID and OXID StateID
     *
     * @param string $countryId
     * @param string $stateId
     * @return string
     */
    private function getStateName(string $countryId, string $stateId): string
    {
        /** @var \OxidSolutionCatalysts\TeleCash\Extension\Application\Model\State $state */
        $state = $this->oxNewService->oxNew(State::class);
        try {
            $state->loadByIdAndCountry(
                $stateId,
                $countryId
            );
            $stateName = $state->getFieldStringData('oxtitle');
        } catch (Exception | DatabaseConnectionException | StandardException) {
            $stateName = '';
        }

        return $stateName;
    }

    /**
     * get Country Name from OXID CountryID
     *
     * @param string $countryId
     * @return string
     */
    private function getCountryName(string $countryId): string
    {
        /** @var \OxidSolutionCatalysts\TeleCash\Extension\Application\Model\Country $country */
        $country = $this->oxNewService->oxNew(Country::class);
        $country->load($countryId);
        return $country->isLoaded() ? $country->getFieldStringData('oxtitle') : '';
    }

    /**
     * create Price-Service
     *
     * @param oxPrice $price
     * @param object $currency
     * @return Price
     */
    private function createPriceService(oxPrice $price, object $currency): Price
    {
        return new Price($price, $currency);
    }
}
