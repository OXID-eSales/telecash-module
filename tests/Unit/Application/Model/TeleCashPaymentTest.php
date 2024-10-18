<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\Application\Model;

use Doctrine\DBAL\Connection;
use OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionProviderInterface;
use OxidSolutionCatalysts\TeleCash\Application\Model\TeleCashPayment;
use OxidSolutionCatalysts\TeleCash\Core\Module;
use OxidSolutionCatalysts\TeleCash\Core\Service\OxNewService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;

/**
 * Class TeleCashPaymentTest
 *
 * This class contains unit tests for the TeleCashPayment model.
 */
class TeleCashPaymentTest extends TestCase
{
    /** @var TeleCashPayment|MockObject The mocked TeleCashPayment object */
    private TeleCashPayment|MockObject $teleCashPayment;

    /** @var Connection|MockObject The mocked database connection */
    private Connection|MockObject $connectionMock;

    /** @var ContainerInterface|MockObject The mocked container */
    private MockObject|ContainerInterface $containerMock;

    /** @var OxNewService|MockObject The mocked OxNewService */
    private OxNewService|MockObject $oxNewServiceMock;

    /**
     * Set up the test environment before each test
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        // Create mock for database connection
        $this->connectionMock = $this->createMock(Connection::class);
        $connectionProviderMock = $this->createMock(ConnectionProviderInterface::class);
        $connectionProviderMock->method('get')->willReturn($this->connectionMock);

        // Create mock for container
        $this->containerMock = $this->createMock(ContainerInterface::class);
        $this->containerMock->method('get')
            ->with(ConnectionProviderInterface::class)
            ->willReturn($connectionProviderMock);

        // Create mock for OxNewService
        $this->oxNewServiceMock = $this->createMock(OxNewService::class);

        // Create a partial mock for TeleCashPayment
        $this->teleCashPayment = $this->createPartialMock(TeleCashPayment::class, [
            'getServiceFromContainer',
            'getPaymentId',
            'setPaymentId',
            'getTeleCashIdent',
            'setTeleCashIdent',
            'getTeleCashCaptureType',
            'setTeleCashCaptureType',
        ]);

        // Configure the mocked TeleCashPayment
        $this->teleCashPayment->method('getServiceFromContainer')
            ->willReturn($connectionProviderMock);

        // Configure oxNewServiceMock to return our mocked TeleCashPayment
        $this->oxNewServiceMock->method('oxNew')
            ->with(TeleCashPayment::class)
            ->willReturn($this->teleCashPayment);

        // Manually set the connection
        $this->setProperty($this->teleCashPayment, 'connection', $this->connectionMock);
    }

    /**
     * Helper method to set protected/private properties
     *
     * @param object $object The object to modify
     * @param string $propertyName The name of the property to set
     * @param mixed $value The value to set
     * @throws ReflectionException
     */
    private function setProperty($object, $propertyName, $value): void
    {
        $reflection = new ReflectionClass(get_class($object));
        $property = $reflection->getProperty($propertyName);
        $property->setValue($object, $value);
    }

    /**
     * Test setting and getting the payment ID
     */
    public function testSetAndGetPaymentId(): void
    {
        $paymentId = 'test_payment_id';

        // Expect setPaymentId to be called once with the test payment ID
        $this->teleCashPayment->expects($this->once())
            ->method('setPaymentId')
            ->with($paymentId);

        // Expect getPaymentId to be called once and return the test payment ID
        $this->teleCashPayment->expects($this->once())
            ->method('getPaymentId')
            ->willReturn($paymentId);

        // Call the methods and assert the result
        $this->teleCashPayment->setPaymentId($paymentId);
        $this->assertEquals($paymentId, $this->teleCashPayment->getPaymentId());
    }

    /**
     * Test setting and getting the TeleCash ident
     */
    public function testSetAndGetTeleCashIdent(): void
    {
        $validIdent = Module::TELECASH_PAYMENT_IDENTS[0];

        // Expect setTeleCashIdent to be called once with the valid ident
        $this->teleCashPayment->expects($this->once())
            ->method('setTeleCashIdent')
            ->with($validIdent);

        // Expect getTeleCashIdent to be called once and return the valid ident
        $this->teleCashPayment->expects($this->once())
            ->method('getTeleCashIdent')
            ->willReturn($validIdent);

        // Call the methods and assert the result
        $this->teleCashPayment->setTeleCashIdent($validIdent);
        $this->assertEquals($validIdent, $this->teleCashPayment->getTeleCashIdent());
    }

    /**
     * Test setting and getting the TeleCash capture type
     */
    public function testSetAndGetTeleCashCaptureType(): void
    {
        $validIdent = Module::TELECASH_PAYMENT_IDENTS[0];
        $validCaptureType = Module::TELECASH_CAPTURE_TYPES[$validIdent][0];

        // Expect setTeleCashCaptureType to be called once with the valid capture type
        $this->teleCashPayment->expects($this->once())
            ->method('setTeleCashCaptureType')
            ->with($validCaptureType);

        // Expect getTeleCashCaptureType to be called once and return the valid capture type
        $this->teleCashPayment->expects($this->once())
            ->method('getTeleCashCaptureType')
            ->willReturn($validCaptureType);

        // Call the methods and assert the result
        $this->teleCashPayment->setTeleCashCaptureType($validCaptureType);
        $this->assertEquals($validCaptureType, $this->teleCashPayment->getTeleCashCaptureType());
    }
}
