<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Core\Service;

interface TranslateServiceInterface
{
    public function translateString(string $ident = '', int $langId = null): string;
}
