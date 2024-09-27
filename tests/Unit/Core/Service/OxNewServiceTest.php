<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\Core\Service;

use Codeception\PHPUnit\TestCase;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Element2ShopRelations;
use OxidSolutionCatalysts\TeleCash\Core\Service\OxNewService;
use OxidSolutionCatalysts\TeleCash\Traits\ServiceContainer;

class OxNewServiceTest extends TestCase
{
    use ServiceContainer;

    /**
     * @covers \OxidSolutionCatalysts\TeleCash\Core\Service\OxNewService::oxNew
     */
    public function testOxNew()
    {
        $oxNewService = $this->getServiceFromContainer(OxNewService::class);
        $order = $oxNewService->oxNew(Order::class);

        $this->assertInstanceOf(Order::class, $order);

        $itemType = 'oxobject2category';
        $relations = $oxNewService->oxNew(Element2ShopRelations::class, [$itemType]);

        $this->assertInstanceOf(Element2ShopRelations::class, $relations);
        // prove constructor args correctly passed
        $this->assertEquals($itemType, $relations->getItemType());
    }
}
