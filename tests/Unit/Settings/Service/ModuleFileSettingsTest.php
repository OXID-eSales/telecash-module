<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\Settings\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleFileSettingsService;
use OxidSolutionCatalysts\TeleCash\Core\Module;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use OxidEsales\Facts\Facts;
use OxidEsales\Eshop\Core\Registry;

class ModuleFileSettingsTest extends TestCase
{
    private $moduleSettingService;
    private $service;
    private $filesystem;
    private $uploadPath = '/var/www/shop/var/uploads/shops/1/modules/' . Module::MODULE_ID . '/certs';

    protected function setUp(): void
    {
        $this->moduleSettingService = $this->createMock(ModuleSettingServiceInterface::class);
        $this->filesystem = $this->createMock(Filesystem::class);

        // Mock Facts and Registry
        $facts = $this->createMock(Facts::class);
        $facts->method('getShopRootPath')->willReturn('/var/www/shop');

        $config = $this->createMock(\OxidEsales\Eshop\Core\Config::class);
        $config->method('getShopId')->willReturn(1);

        $registry = $this->createMock(Registry::class);
        $registry->method('getConfig')->willReturn($config);

        $this->service = new ModuleFileSettingsService($this->moduleSettingService);
        $reflection = new \ReflectionClass($this->service);

        $filesystemProperty = $reflection->getProperty('filesystem');
        $filesystemProperty->setValue($this->service, $this->filesystem);

        $uploadPathProperty = $reflection->getProperty('uploadPath');
        $uploadPathProperty->setValue($this->service, $this->uploadPath);
    }

    public function certificateMethodsProvider(): array
    {
        return [
            'P12' => [
                'storeMethod' => 'storeClientCertificateP12File',
                'checkExistsMethod' => 'checkClientCertificateP12FileExists',
                'getPathMethod' => 'getClientCertificateP12FilePath',
                'settingName' => ModuleFileSettingsService::CLIENT_CERT_P12_FILE,
                'filename' => 'test.p12',
            ],
            'PrivateKey' => [
                'storeMethod' => 'storeClientCertificatePrivateKeyFile',
                'checkExistsMethod' => 'checkClientCertificatePrivateKeyFileExists',
                'getPathMethod' => 'getClientCertificatePrivateKeyFilePath',
                'settingName' => ModuleFileSettingsService::CLIENT_CERT_PRIVATEKEY_FILE,
                'filename' => 'test.key',
            ],
            'PEM' => [
                'storeMethod' => 'storeClientCertificatePEMFile',
                'checkExistsMethod' => 'checkClientCertificatePEMFileExists',
                'getPathMethod' => 'getClientCertificatePEMFilePath',
                'settingName' => ModuleFileSettingsService::CLIENT_CERT_PEM_FILE,
                'filename' => 'test.pem',
            ],
            'TrustAnchor' => [
                'storeMethod' => 'storeTrustAnchorPEMFile',
                'checkExistsMethod' => 'checkTrustAnchorPEMFileExists',
                'getPathMethod' => 'getTrustAnchorPEMFilePath',
                'settingName' => ModuleFileSettingsService::TRUST_ANCHOR_PEM_FILE,
                'filename' => 'trust_anchor.pem',
            ],
        ];
    }

    /**
     * @dataProvider certificateMethodsProvider
     */
    public function testStoreCertificateFile(string $storeMethod, string $checkExistsMethod, string $getPathMethod, string $settingName, string $filename)
    {
        $file = $this->createMock(UploadedFile::class);
        $file->method('getClientOriginalName')->willReturn($filename);

        $this->filesystem->expects($this->once())
            ->method('exists')
            ->willReturn(false);

        $file->expects($this->once())
            ->method('move')
            ->with($this->anything(), $filename);

        $this->moduleSettingService->expects($this->once())
            ->method('saveString')
            ->with($settingName, $filename, Module::MODULE_ID);

        $this->service->$storeMethod($file);

        // Test check exists method
        $this->moduleSettingService->method('getString')
            ->willReturn($filename);

        $this->filesystem->method('exists')
            ->willReturn(true);

        $this->assertTrue($this->service->$checkExistsMethod());

        // Test get path method
        $expectedPath = $this->uploadPath . '/' . $filename;
        $this->assertEquals($expectedPath, $this->service->$getPathMethod());
    }

    public function testGetSafeFilename()
    {
        $unsafeFilename = 'test file@123.txt';
        $expectedSafeFilename = 'testfile123.txt';

        $this->filesystem->method('exists')
            ->willReturn(false);

        $method = new \ReflectionMethod(ModuleFileSettingsService::class, 'getSafeFilename');

        $actualSafeFilename = $method->invoke($this->service, $unsafeFilename);
        $this->assertEquals($expectedSafeFilename, $actualSafeFilename);
    }

    public function testGetSafeFilenameWithExistingFile()
    {
        $filename = 'test.txt';

        $this->filesystem->method('exists')
            ->willReturn(true);

        $method = new \ReflectionMethod(ModuleFileSettingsService::class, 'getSafeFilename');

        $safeFilename = $method->invoke($this->service, $filename);
        $this->assertStringStartsWith('test_', $safeFilename);
        $this->assertStringEndsWith('.txt', $safeFilename);
    }
}