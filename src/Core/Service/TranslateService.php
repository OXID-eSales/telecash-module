<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Core\Service;

/**
 * @extendable-class
 */
class TranslateService implements TranslateServiceInterface
{
    public function __construct(
        private readonly RegistryService $registryService
    ) {
    }

    public function translateString(string $ident = '', int $langId = null): string
    {
        $langObj = $this->registryService->getLang();
        $translation = $langObj->translateString($ident, $langId);

        if ($langObj->isTranslated()) {
            return is_string($translation) ? $translation : '';
        }

        return $ident;
    }
}
