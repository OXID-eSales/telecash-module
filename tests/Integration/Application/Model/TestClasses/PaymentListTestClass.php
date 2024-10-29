<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Integration\Application\Model\TestClasses;

use OxidSolutionCatalysts\TeleCash\Extension\Application\Model\Payment;
use OxidSolutionCatalysts\TeleCash\Extension\Application\Model\PaymentList;
use Psr\Container\ContainerInterface;

/**
 * Test Class for PaymentList Model Extension
 *
 * This class provides a testable version of the PaymentList model by:
 * - Replacing dependencies with controllable mock objects
 * - Providing public access to protected methods for testing
 * - Simulating parent class behavior without OXID framework dependencies
 * - Enabling controlled testing of payment validation and filtering
 *
 * Testing strategy:
 * - Uses dependency injection for service container
 * - Simulates parent payment list behavior
 * - Provides controlled payment model access
 * - Enables isolated testing of payment filtering logic
 *
 * @package OxidSolutionCatalysts\TeleCash\Tests\Integration\Application\Model\TestClasses
 */
class PaymentListTestClass extends PaymentList
{
    /**
     * Map of payment IDs to their validity status
     * Used to control validation results in tests
     *
     * @var array<string, bool>
     */
    private array $paymentValidityMap = [];

    /**
     * Mock payment model for testing
     * Allows controlled behavior of payment operations
     *
     * @var Payment|null
     */
    private ?Payment $mockPayment = null;

    /**
     * Simulated parent payment list
     * Replaces the parent class's payment list retrieval
     *
     * @var array
     */
    private array $parentPaymentList = [];

    /**
     * Creates a new instance of the payment list test class
     *
     * @param bool $initParent Whether to initialize the parent OXID model
     *                         Set to false for isolated testing without framework dependencies
     */
    public function __construct(bool $initParent = true)
    {
        parent::__construct($initParent);
    }

    /**
     * Provides public access to the protected setContainer method
     * Necessary for dependency injection in test environment
     *
     * @param ContainerInterface $container The service container to use
     */
    public function publicSetContainer(ContainerInterface $container): void
    {
        $this->setContainer($container);
    }

    /**
     * Sets a mock payment model for testing
     * Allows tests to control the behavior of payment operations
     *
     * @param Payment|null $payment The mock payment model or null to simulate missing payment
     */
    public function setMockPayment(?Payment $payment = null): void
    {
        $this->mockPayment = $payment;
    }

    /**
     * Overrides the payment model retrieval for testing purposes
     * Returns the configured mock payment model
     *
     * @return Payment|null The configured mock payment model
     */
    protected function getOxidPaymentModel(): ?Payment
    {
        return $this->mockPayment;
    }

    /**
     * Sets the payment list to be returned by getPaymentList
     * Simulates the parent class's payment list retrieval
     *
     * @param array $paymentList The payment list to be used in tests
     */
    public function publicSetParentPaymentList(array $paymentList): void
    {
        $this->parentPaymentList = $paymentList;
    }

    /**
     * Core test implementation of payment list retrieval and filtering
     *
     * This method simulates the actual PaymentList behavior by:
     * - Using the configured parent payment list
     * - Applying TeleCash-specific filtering
     * - Handling payment validation
     *
     * @param string $sShipSetId Shipping set ID
     * @param float $dPrice Price to check
     * @param User|null $oUser User object
     * @return array Filtered payment list
     */
    public function getPaymentList($sShipSetId, $dPrice, $oUser = null)
    {
        $paymentList = $this->parentPaymentList;

        foreach ($paymentList as $oxPaymentId => $paymentListElement) {
            $payment = $this->getOxidPaymentModel();
            /** @var Payment $payment */
            if (
                $payment
                && $payment->load($paymentListElement->getId())
                && !$payment->isTeleCashPaymentValid()
            ) {
                unset($paymentList[$oxPaymentId]);
            }
        }

        return $paymentList;
    }
}
