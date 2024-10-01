<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\Settings\Service;

use OxidEsales\Eshop\Core\Config;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidSolutionCatalysts\TeleCash\Core\Service\RegistryService;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleFileSettingsService;
use OxidSolutionCatalysts\TeleCash\Core\Module;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleFileSettingsServiceInterface;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionMethod;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use OxidEsales\Facts\Facts;
use OxidEsales\Eshop\Core\Registry;

class ModuleFileSettingsTest extends TestCase
{
    private $moduleSettingService;
    private ModuleFileSettingsService $service;
    private $filesystem;

    private string $uploadPath = '/var/www/shop/var/uploads/shops/1/modules/' . Module::MODULE_ID . '/certs';

    protected function setUp(): void
    {
        $this->moduleSettingService = $this->createMock(ModuleSettingServiceInterface::class);
        $this->filesystem = $this->createMock(Filesystem::class);

        // Mock Facts and Registry
        $facts = $this->createMock(Facts::class);
        $facts->method('getShopRootPath')->willReturn('/var/www/shop');

        $config = $this->createMock(Config::class);
        $config->method('getShopId')->willReturn(1);

        $registryService = $this->createMock(RegistryService::class);
        $registryService->method('getConfig')->willReturn($config);

        $this->service = new ModuleFileSettingsService(
            $this->moduleSettingService,
            $registryService
        );
        $reflection = new \ReflectionClass($this->service);

        $filesystemProperty = $reflection->getProperty('filesystem');
        $filesystemProperty->setValue($this->service, $this->filesystem);

        $uploadPathProperty = $reflection->getProperty('uploadPath');
        $uploadPathProperty->setValue($this->service, $this->uploadPath);
    }

    public static function certificateMethodsProvider(): array
    {
        return [
            'P12' => [
                'storeMethod'       => 'storeClientCertificateP12File',
                'deleteMethod'      => 'deleteClientCertificateP12File',
                'checkExistsMethod' => 'checkClientCertificateP12FileExists',
                'getPathMethod'     => 'getClientCertificateP12FilePath',
                'settingName'       => ModuleFileSettingsServiceInterface::CLIENT_CERT_P12_FILE,
                'filename'          => 'test.p12',
            ],
            'PrivateKey' => [
                'storeMethod'       => 'storeClientCertificatePrivateKeyFile',
                'deleteMethod'      => 'deleteClientCertificatePrivateKeyFile',
                'checkExistsMethod' => 'checkClientCertificatePrivateKeyFileExists',
                'getPathMethod'     => 'getClientCertificatePrivateKeyFilePath',
                'settingName'       => ModuleFileSettingsServiceInterface::CLIENT_CERT_PRIVATEKEY_FILE,
                'filename'          => 'test.key',
            ],
            'PEM' => [
                'storeMethod'       => 'storeClientCertificatePEMFile',
                'deleteMethod'      => 'deleteClientCertificatePEMFile',
                'checkExistsMethod' => 'checkClientCertificatePEMFileExists',
                'getPathMethod'     => 'getClientCertificatePEMFilePath',
                'settingName'       => ModuleFileSettingsServiceInterface::CLIENT_CERT_PEM_FILE,
                'filename'          => 'test.pem',
            ],
            'TrustAnchor' => [
                'storeMethod'       => 'storeTrustAnchorPEMFile',
                'deleteMethod'      => 'deleteTrustAnchorPEMFile',
                'checkExistsMethod' => 'checkTrustAnchorPEMFileExists',
                'getPathMethod'     => 'getTrustAnchorPEMFilePath',
                'settingName'       => ModuleFileSettingsServiceInterface::TRUST_ANCHOR_PEM_FILE,
                'filename'          => 'trust_anchor.pem',
            ],
        ];
    }

    /**
     * @dataProvider certificateMethodsProvider
     */
    public function testCertificateFileMethods(
        string $storeMethod,
        string $deleteMethod,
        string $checkExistsMethod,
        string $getPathMethod,
        string $settingName,
        string $filename
    ): void {
        $file = $this->createMock(UploadedFile::class);
        $file->method('getClientOriginalName')->willReturn($filename);

        // Test store method
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

        // Test delete method
        $this->filesystem->expects($this->once())
            ->method('remove')
            ->with($expectedPath);

        $this->moduleSettingService->expects($this->once())
            ->method('saveString')
            ->with($settingName, '', Module::MODULE_ID);

        $this->assertTrue($this->service->$deleteMethod());
    }

    public function testDeleteNonExistentFile(): void
    {
        $this->moduleSettingService->method('getString')
            ->willReturn('');

        $this->assertFalse($this->service->deleteClientCertificateP12File());
    }

    public function testDeleteFileIOException(): void
    {
        $this->moduleSettingService->method('getString')
            ->willReturn('test.p12');

        $this->filesystem->method('exists')
            ->willReturn(true);

        $this->filesystem->method('remove')
            ->willThrowException(new IOException('Test exception'));

        $this->assertFalse($this->service->deleteClientCertificateP12File());
    }

    /**
     * @throws ReflectionException
     */
    public function testGetSafeFilename(): void
    {
        $unsafeFilename = 'test file@123.txt';
        $expectedSafeFilename = 'testfile123.txt';

        $this->filesystem->method('exists')
            ->willReturn(false);

        $method = new ReflectionMethod(
            ModuleFileSettingsService::class,
            'getSafeFilename'
        );

        $actualSafeFilename = $method->invoke($this->service, $unsafeFilename);
        $this->assertEquals($expectedSafeFilename, $actualSafeFilename);
    }

    /**
     * @throws ReflectionException
     */
    public function testGetSafeFilenameWithExistingFile(): void
    {
        $filename = 'test.txt';

        $this->filesystem->method('exists')
            ->willReturn(true);

        $method = new ReflectionMethod(
            ModuleFileSettingsService::class,
            'getSafeFilename'
        );

        $safeFilename = $method->invoke($this->service, $filename);
        $this->assertStringStartsWith('test_', $safeFilename);
        $this->assertStringEndsWith('.txt', $safeFilename);
    }
}
