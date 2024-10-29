<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Integration\Extension\Application\Model;

use OxidEsales\Eshop\Application\Model\Payment as CorePayment;
use OxidEsales\Eshop\Application\Model\User;
use OxidSolutionCatalysts\TeleCash\Extension\Application\Model\Payment;
use OxidSolutionCatalysts\TeleCash\Tests\Integration\Application\Model\TestClasses\PaymentListTestClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Container\ContainerInterface;

/**
 * Integration Test for PaymentList Model Extension
 *
 * This test suite verifies the payment list filtering functionality, specifically:
 * - Correct filtering of invalid TeleCash payments
 * - Proper handling of payment loading failures
 * - Graceful handling of missing payment models
 *
 * Testing strategy:
 * - Uses mock objects for dependencies
 * - Tests both success and failure scenarios
 * - Verifies payment filtering logic
 * - Ensures proper handling of edge cases
 *
 * @package OxidSolutionCatalysts\TeleCash\Tests\Integration\Extension\Application\Model
 */
class PaymentListTest extends TestCase
{
    /**
     * The payment list test class instance
     * @var PaymentListTestClass
     */
    private PaymentListTestClass $paymentList;

    /**
     * Mock container for dependency injection
     * @var ContainerInterface&MockObject
     */
    private ContainerInterface&MockObject $container;

    /**
     * Mock payment model for testing
     * @var Payment&MockObject
     */
    private Payment&MockObject $payment;

    /**
     * Set up the test environment
     *
     * Initializes:
     * - Mock objects for dependencies
     * - Payment list test class
     * - Container configuration
     */
    protected function setUp(): void
    {
        // Create mocks
        $this->container = $this->createMock(ContainerInterface::class);
        $this->payment = $this->createMock(Payment::class);

        // Setup container mock
        $this->container->method('has')
            ->willReturn(true);

        // Initialize payment list model without parent
        $this->paymentList = new PaymentListTestClass(false);
        $this->paymentList->publicSetContainer($this->container);
        $this->paymentList->setMockPayment($this->payment);
    }

    /**
     * Tests payment list filtering functionality
     *
     * Verifies that:
     * - Valid TeleCash payments are retained
     * - Invalid TeleCash payments are removed
     * - Filtering works correctly with multiple payments
     */
    public function testGetPaymentListFiltering(): void
    {
        // Create mock payments for the parent result
        $parentPayments = [
            'payment1' => $this->createConfiguredMock(CorePayment::class, ['getId' => 'payment1']),
            'payment2' => $this->createConfiguredMock(CorePayment::class, ['getId' => 'payment2']),
            'payment3' => $this->createConfiguredMock(CorePayment::class, ['getId' => 'payment3']),
        ];

        // Track the current payment ID being validated
        $currentPaymentId = null;

        // Setup payment validation chain
        $this->payment->method('load')
            ->willReturnCallback(function ($id) use (&$currentPaymentId) {
                $currentPaymentId = $id;
                return true;
            });

        $this->payment->method('getId')
            ->willReturnCallback(function () use (&$currentPaymentId) {
                return $currentPaymentId;
            });

        $this->payment->method('isTeleCashPaymentValid')
            ->willReturnCallback(function () use (&$currentPaymentId) {
                return match ($currentPaymentId) {
                    'payment1' => true,
                    'payment2' => false,  // This payment should be filtered out
                    'payment3' => true,
                    default => true
                };
            });

        $this->paymentList->publicSetParentPaymentList($parentPayments);

        $result = $this->paymentList->getPaymentList('shipset1', 100.0, $this->createMock(User::class));

        // Debug output if test fails
        if (count($result) !== 2) {
            var_dump(array_keys($result));
            var_dump(array_map(function ($payment) {
                return $payment->getId();
            }, $result));
        }

        $this->assertCount(2, $result);
        $this->assertArrayHasKey('payment1', $result);
        $this->assertArrayHasKey('payment3', $result);
        $this->assertArrayNotHasKey('payment2', $result);
    }

    /**
     * Tests payment list handling when payment loading fails
     *
     * Verifies that:
     * - Payments are retained when loading fails
     * - System continues to function despite load failures
     */
    public function testGetPaymentListWithLoadFailure(): void
    {
        $parentPayments = [
            'payment1' => $this->createConfiguredMock(CorePayment::class, ['getId' => 'payment1']),
        ];

        $this->payment->method('load')
            ->willReturn(false);

        $this->paymentList->publicSetParentPaymentList($parentPayments);

        $result = $this->paymentList->getPaymentList('shipset1', 100.0, $this->createMock(User::class));

        $this->assertCount(1, $result);
        $this->assertArrayHasKey('payment1', $result);
    }

    /**
     * Tests payment list handling when payment model is unavailable
     *
     * Verifies that:
     * - System handles missing payment models gracefully
     * - Payments are retained when model is unavailable
     */
    public function testGetPaymentListWithoutPaymentModel(): void
    {
        $parentPayments = [
            'payment1' => $this->createConfiguredMock(CorePayment::class, ['getId' => 'payment1']),
        ];

        $this->paymentList->setMockPayment(null);
        $this->paymentList->publicSetParentPaymentList($parentPayments);

        $result = $this->paymentList->getPaymentList('shipset1', 100.0, $this->createMock(User::class));

        $this->assertCount(1, $result);
        $this->assertArrayHasKey('payment1', $result);
    }
}
