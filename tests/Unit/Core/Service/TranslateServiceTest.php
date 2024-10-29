<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\Core\Service;

use OxidEsales\Eshop\Core\Language;
use OxidSolutionCatalysts\TeleCash\Core\Service\RegistryService;
use OxidSolutionCatalysts\TeleCash\Core\Service\TranslateService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(TranslateService::class)]
class TranslateServiceTest extends TestCase
{
    private TranslateService $translateService;
    private MockObject|Language $languageMock;
    private RegistryService|MockObject $registryServiceMock;

    protected function setUp(): void
    {
        $this->languageMock = $this->createMock(Language::class);

        $this->registryServiceMock = $this->createMock(RegistryService::class);
        $this->registryServiceMock->method('getLang')->willReturn($this->languageMock);

        $this->translateService = new TranslateService($this->registryServiceMock);
    }

    public function testTranslateStringWhenTranslationExists(): void
    {
        $ident = 'TEST_IDENT';
        $expectedTranslation = 'Translated String';

        $this->languageMock->method('translateString')->willReturn($expectedTranslation);
        $this->languageMock->method('isTranslated')->willReturn(true);

        $result = $this->translateService->translateString($ident);

        $this->assertEquals($expectedTranslation, $result);
    }

    public function testTranslateStringWhenTranslationDoesNotExist(): void
    {
        $ident = 'NON_EXISTENT_IDENT';

        $this->languageMock->method('translateString')->willReturn($ident);
        $this->languageMock->method('isTranslated')->willReturn(false);

        $result = $this->translateService->translateString($ident);

        $this->assertEquals($ident, $result);
    }

    public function testTranslateStringWithSpecificLanguage(): void
    {
        $ident = 'LANG_SPECIFIC_IDENT';
        $langId = 1; // Assuming 1 represents a specific language
        $expectedTranslation = 'Language Specific Translation';

        $this->languageMock->method('translateString')
            ->with($ident, $langId)
            ->willReturn($expectedTranslation);
        $this->languageMock->method('isTranslated')->willReturn(true);

        $result = $this->translateService->translateString($ident, $langId);

        $this->assertEquals($expectedTranslation, $result);
    }

    public function testTranslateStringReturnsEmptyStringWhenTranslationIsNotString(): void
    {
        $ident = 'NON_STRING_TRANSLATION';

        $this->languageMock->method('translateString')->willReturn(['not', 'a', 'string']);
        $this->languageMock->method('isTranslated')->willReturn(true);

        $result = $this->translateService->translateString($ident);

        $this->assertEquals('', $result);
    }
}
