<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Application\Model;

use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidSolutionCatalysts\TeleCash\Core\Module;
use OxidSolutionCatalysts\TeleCash\Traits\DataGetter;
use OxidSolutionCatalysts\TeleCash\Traits\ServiceContainer;

class TeleCashPayment extends BaseModel
{
    use ServiceContainer;
    use DataGetter;

    /**
     * Current class name
     *
     * @var string
     */
    protected $_sClassName = 'OxidSolutionCatalysts\TeleCash\Application\TeleCashPayment';

    /**
     * Core table name
     *
     * @var string
     */
    protected $_sCoreTable = Module::TELECASH_PAYMENT_EXTENSION_TABLE;

    public function __construct()
    {
        parent::__construct();
        $this->init($this->_sCoreTable);
    }
}
