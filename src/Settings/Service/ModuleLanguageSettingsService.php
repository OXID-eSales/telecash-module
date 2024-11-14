<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Settings\Service;

use OxidEsales\EshopCommunity\Core\Exception\LanguageNotFoundException;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidSolutionCatalysts\TeleCash\Core\Module;
use OxidSolutionCatalysts\TeleCash\Core\Service\RegistryService;

/**
 * Class ModuleLanguageSettingsService
 *
 * This service deals with language specific topics
 *
 */
class ModuleLanguageSettingsService implements ModuleLanguageSettingsServiceInterface
{
    /**
     * ModuleLanguageSettingsService constructor.
     *
     * @param ModuleSettingServiceInterface $moduleSettingService Service for storing module settings
     */
    public function __construct(
        private readonly ModuleSettingServiceInterface $moduleSettingService,
        private readonly RegistryService $registryService
    ) {
    }

    /**
     * get the iso to locale collection from Config
     * @return array<string, string> Key is language ISO code, value is locale (e.g. 'en' => 'en_US')
     */
    public function getLanguages(): array
    {
        return $this->moduleSettingService->getCollection(
            ModuleLanguageSettingsServiceInterface::LANGUAGES,
            Module::MODULE_ID
        );
    }

    /**
     * save the iso to locale collection to Config
     * @param array<string, string> $value Key is language ISO code, value is locale (e.g. 'en' => 'en_US')
     * @return void
     */
    public function saveLanguages(array $value): void
    {
        $this->moduleSettingService->saveCollection(
            ModuleLanguageSettingsServiceInterface::LANGUAGES,
            $value,
            Module::MODULE_ID
        );
    }

    /**
     * get a Locale for provided Language ISO-Code e.g.: en => en_US
     *
     * @param string $countryIso
     * @return string
     */
    public function getLocaleForCountryIso(string $countryIso = ''): string
    {
        $countryIso = $countryIso ?: $this->getOxidLanguageIso();

        // make sure provided keys and searched keys are in lower case
        $countryIso = strtolower($countryIso);

        $languageSettings = $this->getLanguages();
        $languages = array_change_key_case($languageSettings);
        return isset($languages[$countryIso]) ?
            (string)$languages[$countryIso] :
            ModuleLanguageSettingsServiceInterface::DEFAULT_LOCALE;
    }

    /**
     * provide the actual Shop-Language
     *
     * @return string
     */
    private function getOxidLanguageIso(): string
    {
        try {
            $oxidLanguage = $this->registryService->getLang()->getLanguageAbbr();
        } catch (LanguageNotFoundException) {
            $oxidLanguage = ModuleLanguageSettingsServiceInterface::DEFAULT_OXID_LANGUAGE;
        }

        return $oxidLanguage;
    }
}
