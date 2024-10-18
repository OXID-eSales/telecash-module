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
use RuntimeException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use OxidEsales\Facts\Facts;
use Symfony\Component\String\UnicodeString;

/**
 * Test case for ModuleFileSettingsService.
 *
 * This class contains tests for the certificate file handling functionality
 * of the ModuleFileSettingsService. It covers various operations on different
 * types of certificate files used in the TeleCash module.
 *
 * The tests in this class are designed to run in a CI/CD pipeline and ensure
 * the robustness of file operations across different certificate types.
 */
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
                'getNameMethod'     => 'getClientCertificateP12FileName',
                'getPathMethod'     => 'getClientCertificateP12FilePath',
                'settingName'       => ModuleFileSettingsServiceInterface::CLIENT_CERT_P12_FILE,
                'filename'          => 'test.p12',
            ],
            'PrivateKey' => [
                'storeMethod'       => 'storeClientCertificatePrivateKeyFile',
                'deleteMethod'      => 'deleteClientCertificatePrivateKeyFile',
                'checkExistsMethod' => 'checkClientCertificatePrivateKeyFileExists',
                'getNameMethod'     => 'getClientCertificatePrivateKeyFileName',
                'getPathMethod'     => 'getClientCertificatePrivateKeyFilePath',
                'settingName'       => ModuleFileSettingsServiceInterface::CLIENT_CERT_PRIVATEKEY_FILE,
                'filename'          => 'test.key',
            ],
            'TrustAnchor' => [
                'storeMethod'       => 'storeTrustAnchorPEMFile',
                'deleteMethod'      => 'deleteTrustAnchorPEMFile',
                'checkExistsMethod' => 'checkTrustAnchorPEMFileExists',
                'getNameMethod'     => 'getTrustAnchorPEMFileName',
                'getPathMethod'     => 'getTrustAnchorPEMFilePath',
                'settingName'       => ModuleFileSettingsServiceInterface::TRUST_ANCHOR_PEM_FILE,
                'filename'          => 'trust_anchor.pem',
            ],
        ];
    }

    /**
     * Test the certificate file handling methods in ModuleFileSettingsService.
     *
     * This test covers the full lifecycle of certificate file operations:
     * storing, checking existence, retrieving name and path, and deleting.
     * It uses data providers to test multiple certificate types.
     *
     * Key aspects of this test:
     * 1. Mocking complex filesystem operations and ModuleSettingService interactions.
     * 2. Simulating the behavior of the private getSafeFilename method.
     * 3. Verifying multiple calls to saveString method with different arguments.
     * 4. Ensuring compatibility with newer PHPUnit versions by using callbacks
     *    instead of deprecated methods like withConsecutive().
     *
     * The test is designed to be flexible, allowing for both the original filename
     * to be used (if it's already safe) or a modified filename with a timestamp.
     *
     * @dataProvider certificateMethodsProvider
     * @param string $storeMethod Method name for storing the certificate
     * @param string $deleteMethod Method name for deleting the certificate
     * @param string $checkExistsMethod Method name for checking if the certificate exists
     * @param string $getNameMethod Method name for getting the certificate filename
     * @param string $getPathMethod Method name for getting the certificate file path
     * @param string $settingName Setting name used in ModuleSettingService
     * @param string $filename Original filename of the certificate
     */
    public function testCertificateFileMethods(
        string $storeMethod,
        string $deleteMethod,
        string $checkExistsMethod,
        string $getNameMethod,
        string $getPathMethod,
        string $settingName,
        string $filename
    ): void {
        $file = $this->createMock(UploadedFile::class);
        $file->method('getClientOriginalName')->willReturn($filename);

        // Capture the actual safe filename generated
        $actualSafeFilename = '';
        $this->filesystem->expects($this->atLeastOnce())
            ->method('exists')
            ->willReturnCallback(function ($path) use (&$actualSafeFilename) {
                if (basename($path) === $actualSafeFilename) {
                    return true; // File exists after it's been "stored"
                }
                $actualSafeFilename = basename($path);
                return false; // File doesn't exist initially
            });

        $file->expects($this->once())
            ->method('move')
            ->with(
                $this->equalTo($this->uploadPath),
                $this->callback(function ($arg) use (&$actualSafeFilename) {
                    $actualSafeFilename = $arg;
                    return true;
                })
            );

        $this->moduleSettingService->expects($this->atLeastOnce())
            ->method('getString')
            ->with($settingName, Module::MODULE_ID)
            ->willReturnCallback(function () use (&$actualSafeFilename) {
                return new UnicodeString($actualSafeFilename);
            });

        $saveStringCalls = 0;
        $this->moduleSettingService->expects($this->atLeastOnce())
            ->method('saveString')
            ->willReturnCallback(function ($name, $value, $moduleId) use ($settingName, &$saveStringCalls) {
                $this->assertEquals($settingName, $name);
                $this->assertEquals(Module::MODULE_ID, $moduleId);
                if ($saveStringCalls === 0) {
                    $this->assertNotEmpty($value);
                } else {
                    $this->assertEmpty($value);
                }
                $saveStringCalls++;
            });

        $this->service->$storeMethod($file);

        // Verify the safe filename format
        $this->assertThat(
            $actualSafeFilename,
            $this->logicalOr(
                $this->equalTo($filename),
                $this->matchesRegularExpression('/^[a-z0-9_]+_\d+\.[a-z]+$/')
            )
        );

        // Test check exists method
        $this->assertTrue($this->service->$checkExistsMethod());

        // Test get name method
        $this->assertEquals($actualSafeFilename, $this->service->$getNameMethod());

        // Test get path method
        $expectedPath = $this->uploadPath . '/' . $actualSafeFilename;
        $this->assertEquals($expectedPath, $this->service->$getPathMethod());

        // Test delete method
        $this->filesystem->expects($this->once())
            ->method('remove')
            ->with($expectedPath);

        $this->assertTrue($this->service->$deleteMethod());

        // Ensure saveString was called at least twice
        $this->assertGreaterThanOrEqual(2, $saveStringCalls);
    }

    public function testDeleteNonExistentFile(): void
    {
        $this->moduleSettingService->method('getString')
            ->willReturn(new UnicodeString('non_existent_file.p12'));

        $this->filesystem->method('exists')
            ->willReturn(false);

        $this->moduleSettingService->expects($this->never())
            ->method('saveString');

        $this->filesystem->expects($this->never())
            ->method('remove');

        $this->assertFalse($this->service->deleteClientCertificateP12File());
    }

    public function testDeleteFileIOException(): void
    {
        $this->moduleSettingService->method('getString')
            ->willReturn(new UnicodeString('test.p12'));

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

    public function testCreateDirectoryMotExistsWithExpectedException()
    {
        $foldername = '/home/root/test';

        $this->filesystem->method('exists')
            ->willThrowException(new IOException('Test exception'));

        $method = new ReflectionMethod(
            ModuleFileSettingsService::class,
            'createDirectoryIfNotExists'
        );

        $this->expectException(RuntimeException::class);
        $method->invoke($this->service, $foldername);
    }
}
