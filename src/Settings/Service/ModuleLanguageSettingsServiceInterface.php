<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Settings\Service;

interface ModuleLanguageSettingsServiceInterface
{
    public const DEFAULT_LOCALE = 'en_US';
    public const DEFAULT_OXID_LANGUAGE = 'en';

    public const LANGUAGES = 'osctelecash_languages';

    /**
     * get the iso to locale collection from Config
     * @return array<string, string> Key is language ISO code, value is locale (e.g. 'en' => 'en_US')
     */
    public function getLanguages(): array;

    /**
     * save the iso to locale collection to Config
     * @param array<string, string> $value Key is language ISO code, value is locale (e.g. 'en' => 'en_US')
     * @return void
     */
    public function saveLanguages(array $value): void;

    /**
     * get a Locale for provided Language ISO-Code e.g.: en => en_US
     *
     * @param string $countryIso
     * @return string
     */
    public function getLocaleForCountryIso(string $countryIso = ''): string;
}
