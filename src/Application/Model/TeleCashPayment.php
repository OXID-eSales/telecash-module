<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Application\Model;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionProviderInterface;
use OxidSolutionCatalysts\TeleCash\Exception\TeleCashException;
use OxidSolutionCatalysts\TeleCash\Application\Model\Interface\TeleCashPaymentInterface;
use OxidSolutionCatalysts\TeleCash\Core\Module;
use OxidSolutionCatalysts\TeleCash\Core\Service\TeleCashPaymentValidatorService;
use OxidSolutionCatalysts\TeleCash\Traits\DataGetter;
use OxidSolutionCatalysts\TeleCash\Traits\ServiceContainer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class TeleCashPayment extends BaseModel implements TeleCashPaymentInterface
{
    use ServiceContainer;
    use DataGetter;

    protected ?string $paymentId = null;
    protected ?string $telecashIdent = null;
    protected ?string $captureType = null;

    protected Connection $connection;

    protected $_sClassName = 'OxidSolutionCatalysts\TeleCash\Application\TeleCashPayment';
    protected $_sCoreTable = Module::TELECASH_PAYMENT_EXTENSION_TABLE;

    /**
     * Constructor for TeleCashPayment.
     *
     * Initializes a new instance of the TeleCashPayment class. This constructor can be used
     * in both production and test environments due to its flexible parameter configuration.
     *
     * @param TeleCashPaymentValidatorService $validator   The validator service to check payment existence
     * @param Connection|null                 $connection  Optional database connection. If null, the connection
     *                                                    will be retrieved from the service container.
     *                                                    Primarily used for testing.
     * @param bool                           $initParent  Whether to initialize the parent BaseModel.
     *                                                    Set to false in test environment to avoid
     *                                                    OXID framework dependencies. Default is true.
     *
     * @throws ContainerExceptionInterface     If there's an error with the service container
     * @throws NotFoundExceptionInterface      If a required service is not found in the container
     */
    public function __construct(
        private readonly TeleCashPaymentValidatorService $validator,
        ?Connection $connection = null,
        bool $initParent = true
    ) {
        if ($initParent) {
            parent::__construct();
        }

        $this->init($this->_sCoreTable);
        $this->connection = $connection ?? $this->getServiceFromContainer(ConnectionProviderInterface::class)->get();
    }

    /**
     * Sets values directly for testing purposes.
     *
     * This method provides a way to set the internal state of the payment object
     * without relying on the OXID framework's assign() method. It is specifically
     * designed for testing scenarios where we want to avoid the complexity and
     * dependencies of the framework's data handling.
     *
     * IMPORTANT: This method should only be used in test environments. In production code,
     * always use the standard setters or assign() method to ensure proper data handling
     * and validation.
     *
     * @param string $paymentId    The payment ID to set. This corresponds to the OXPAYMENTID field.
     * @param string $ident        The TeleCash ident to set. Must be one of the valid idents
     *                            defined in Module::TELECASH_PAYMENT_IDENTS.
     * @param string $captureType  The capture type to set. Must be one of the valid types
     *                            defined in Module::TELECASH_CAPTURE_TYPES for the given ident.
     *
     * @internal This method is intended for testing purposes only
     */
    public function setTestValues(string $paymentId, string $ident, string $captureType): void
    {
        $this->paymentId = $paymentId;
        $this->telecashIdent = $ident;
        $this->captureType = $captureType;
    }

    /**
     * Loads TeleCash-Payment by using paymentid instead of oxid.
     *
     * @param string $paymentId content load ID
     *
     * @return bool
     */
    public function loadByPaymentId(string $paymentId = ''): bool
    {
        //getting at least one field before lazy loading the object
        $this->addField('oxid', 0);

        $table = $this->getViewName();
        $shopId = $this->getShopId();

        $query = $this->buildSelectString([
            $table . '.' . Module::TELECASH_PAYMENT_EXTENSION_TABLE_OXPAYMENTID => $paymentId,
            $table . '.oxshopid'                                                => $shopId
        ]);

        try {
            $result = $this->connection->fetchAssociative($query);
            if (is_array($result)) {
                $this->assign($result);
                $this->_isLoaded = true;
            }
        } catch (Exception) {
            $this->_isLoaded = false;
        }

        return $this->isLoaded();
    }

    /** setter for PaymentId */
    public function setPaymentId(string $paymentId): void
    {
        $this->paymentId = $paymentId;
        $this->assign([Module::TELECASH_PAYMENT_EXTENSION_TABLE_OXPAYMENTID => $this->paymentId]);
    }

    /** getter for PaymentId */
    public function getPaymentId(): string
    {
        if (is_null($this->paymentId)) {
            $this->paymentId = $this->getFieldStringData(Module::TELECASH_PAYMENT_EXTENSION_TABLE_OXPAYMENTID);
        }
        return $this->paymentId;
    }

    /** getter for possible TeleCash Idents */
    public function getPossibleTeleCashIdents(): array
    {
        return Module::TELECASH_PAYMENT_IDENTS;
    }

    /** getter for TeleCash Ident */
    public function getTeleCashIdent(): string
    {
        if (is_null($this->telecashIdent)) {
            $this->telecashIdent = $this->getFieldStringData(Module::TELECASH_PAYMENT_EXTENSION_TABLE_IDENT);
        }
        return $this->telecashIdent;
    }

    /** setter for TeleCash Ident */
    public function setTeleCashIdent(string $ident = ''): void
    {
        $this->telecashIdent = $this->validTeleCashIdent($ident);

        $this->assign([Module::TELECASH_PAYMENT_EXTENSION_TABLE_IDENT => $this->telecashIdent]);
    }

    /** validate the TeleCash Ident */
    public function validTeleCashIdent(string $ident = ''): string
    {
        return $ident && in_array($ident, $this->getPossibleTeleCashIdents(), true) ?
            $ident :
            Module::TELECASH_PAYMENT_IDENT_DEFAULT;
    }

    /** getter for possible TeleCash Capture-Types */
    public function getPossibleTeleCashCaptureTypes(string $ident = ''): array
    {
        $ident = $this->validTeleCashIdent($ident);
        return Module::TELECASH_CAPTURE_TYPES[$ident];
    }

    /** getter for Capture Type */
    public function getTeleCashCaptureType(): string
    {
        if (is_null($this->captureType)) {
            $this->captureType = $this->getFieldStringData(Module::TELECASH_PAYMENT_EXTENSION_TABLE_CAPTURETYPE);
        }
        return $this->captureType;
    }

    /** setter for Capture Type */
    public function setTeleCashCaptureType(string $captureType = ''): void
    {
        $this->captureType = $this->validTeleCashCaptureType($captureType);

        $this->assign([Module::TELECASH_PAYMENT_EXTENSION_TABLE_CAPTURETYPE => $this->captureType]);
    }

    /** validate the TeleCash Ident */
    public function validTeleCashCaptureType(string $captureType = '', string $ident = ''): string
    {
        $ident = $this->validTeleCashIdent($ident);
        $captureTypes = $this->getPossibleTeleCashCaptureTypes($ident);
        return $captureType && in_array($captureType, $captureTypes, true) ?
            $captureType :
            Module::TELECASH_CAPTURE_TYPE_DIRECT;
    }

    /**
     * Validates if the payment exists before saving
     */
    public function validateBeforeSave(): bool
    {
        return $this->validator->checkIfPaymentExists(
            $this->getPaymentId(),
            $this->getTeleCashIdent(),
            $this->getTeleCashCaptureType()
        );
    }

    /**
     * OXID Core
     *
     * {@inheritDoc}
     *
     * @throws TeleCashException
     *
     * @return string|bool
     */
    public function save()
    {
        $result = false;

        if ($this->validateBeforeSave()) {
            $result = parent::save();
        }

        return $result;
    }
}
