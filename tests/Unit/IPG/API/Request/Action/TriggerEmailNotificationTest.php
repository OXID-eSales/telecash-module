<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Request\Action;

use Prophecy\Prophet;

class TriggerEmailNotificationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param string|null $orderId
     * @param string|null $tDate
     * @param string|null $email
     *
     * @dataProvider dataProvider
     */
    public function testXMLDataCreation(string $orderId, string $tDate, string|null $email = null)
    {
        $prophet = new Prophet();
        $orderService  = $prophet->prophesize('OxidSolutionCatalysts\TeleCash\IPG\API\Service\OrderService');

        $triggerEmailNotification = new TriggerEmailNotification(
            $orderService->reveal(),
            $orderId,
            $tDate,
            $email
        );

        $document = $triggerEmailNotification->getDocument();
        $document->appendChild($triggerEmailNotification->getElement());

        $elementValidate = $document->getElementsByTagName('ns2:SendEMailNotification');
        $this->assertEquals(1, $elementValidate->length, 'Expected element SendEMailNotification not found');

        $children = [];
        /** @var \DOMNode $child */
        foreach ($elementValidate->item(0)->childNodes as $child) {
            $children[$child->nodeName] = $child->nodeValue;
        }

        if ($email !== null) {
            $this->assertArrayHasKey('ns2:Email', $children, 'Expected element Email not found');
            $this->assertEquals($email, $children['ns2:Email'], 'Email did not match');
        } else {
            $this->assertArrayNotHasKey('ns2:Email', $children, 'Unexpected element Email was found');
        }
        $this->assertArrayHasKey('ns2:OrderId', $children, 'Expected element OrderId not found');
        $this->assertEquals($orderId, $children['ns2:OrderId'], 'OrderId did not match');
        $this->assertArrayHasKey('ns2:TDate', $children, 'Expected element TDate not found');
        $this->assertEquals($tDate, $children['ns2:TDate'], 'TDate did not match');
    }

    /**
     * Provides some test values
     *
     * @return array
     */
    public static function dataProvider(): array
    {
        return [
            ['12345', '2024-12-31 12:00:00', 'someone@example.com'],
            ['12345', '2024-12-31 12:00:00', null],
        ];
    }
}
