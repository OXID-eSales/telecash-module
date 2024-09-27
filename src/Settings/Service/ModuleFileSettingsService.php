<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Settings\Service;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidEsales\Facts\Facts;
use OxidSolutionCatalysts\TeleCash\Core\Module;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

/**
 * Class ModuleFileSettingsService
 *
 * This service handles the storage and retrieval of certificate files for the TeleCash module.
 * It manages different types of certificate files, preserving their original names while ensuring unique storage.
 * File names are stored using ModuleSettingServiceInterface for persistence.
 */
class ModuleFileSettingsService implements ModuleFileSettingsServiceInterface
{
    private Filesystem $filesystem;

    private string $uploadPath;

    /**
     * ModuleFileSettingsService constructor.
     *
     * @param ModuleSettingServiceInterface $moduleSettingService Service for storing module settings
     */
    public function __construct(
        private readonly ModuleSettingServiceInterface $moduleSettingService
    ) {
        $this->filesystem = new Filesystem();
        $this->uploadPath = $this->initializeUploadPath();
    }

    /**
     * Initialize and get the full path to the upload directory.
     *
     * @return string The full path to the upload directory
     * @throws RuntimeException If there's an error creating the directory
     */
    private function initializeUploadPath(): string
    {
        $facts = new Facts();
        $path = $facts->getShopRootPath() . DIRECTORY_SEPARATOR
            . 'var' . DIRECTORY_SEPARATOR
            . 'uploads' . DIRECTORY_SEPARATOR
            . 'shops' . DIRECTORY_SEPARATOR
            . Registry::getConfig()->getShopId() . DIRECTORY_SEPARATOR
            . 'modules' . DIRECTORY_SEPARATOR
            . Module::MODULE_ID . DIRECTORY_SEPARATOR
            . 'certs';

        $this->createDirectoryIfNotExists($path);
        return $path;
    }

    /**
     * Create a directory if it doesn't exist.
     *
     * @param string $path The path to create
     * @throws RuntimeException If there's an error creating the directory
     */
    private function createDirectoryIfNotExists(string $path): void
    {
        try {
            if (!$this->filesystem->exists($path)) {
                $this->filesystem->mkdir($path);
            }
        } catch (IOExceptionInterface $exception) {
            throw new RuntimeException(
                sprintf(
                    'An error occurred while creating your directory at %s',
                    $exception->getPath()
                )
            );
        }
    }

    /**
     * Store an uploaded file in the designated directory and save its name.
     *
     * @param UploadedFile $file The uploaded file
     * @param string $settingName The setting name to store the filename
     * @return string The new filename of the stored file
     * @throws RuntimeException If there's an error moving the file
     */
    private function storeUploadedFile(UploadedFile $file, string $settingName): string
    {
        $originalFilename = $file->getClientOriginalName();
        $safeFilename = $this->getSafeFilename($originalFilename);

        $file->move($this->uploadPath, $safeFilename);
        $this->moduleSettingService->saveString($settingName, $safeFilename, Module::MODULE_ID);
        return $safeFilename;
    }

    /**
     * Generate a safe filename from the original filename.
     *
     * @param string $filename The original filename
     * @return string A safe version of the filename
     */
    private function getSafeFilename(string $filename): string
    {
        $safeFilename = preg_replace('/[^a-z.-_]/', '', $filename);
        if ($safeFilename === null || $safeFilename === '') {
            return 'file_' . time();
        }

        if ($this->filesystem->exists($this->uploadPath . '/' . $safeFilename)) {
            $pathInfo = pathinfo($safeFilename);
            $extension = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '';
            return $pathInfo['filename'] . '_' . time() . $extension;
        }

        return $safeFilename;
    }

    /**
     * Check if a file exists in the upload directory.
     *
     * @param string $settingName The setting name of the file to check
     * @return bool True if the file exists, false otherwise
     */
    private function checkFileExists(string $settingName): bool
    {
        $filename = $this->moduleSettingService->getString($settingName, Module::MODULE_ID);
        return $this->filesystem->exists($this->uploadPath . '/' . $filename);
    }

    /**
     * Get the full path to a file in the upload directory.
     *
     * @param string $settingName The setting name of the file
     * @return string The full path to the file
     * @throws RuntimeException If no file has been stored for the given setting name
     */
    private function getFilePath(string $settingName): string
    {
        $filename = $this->moduleSettingService->getString($settingName, Module::MODULE_ID);
        return $this->uploadPath . '/' . $filename;
    }

    /**
     * Store the uploaded Client Certificate P12 File.
     *
     * @param UploadedFile $file The uploaded P12 file
     * @throws RuntimeException If there's an error storing the file
     */
    public function storeClientCertificateP12File(UploadedFile $file): void
    {
        $this->storeUploadedFile($file, self::CLIENT_CERT_P12_FILE);
    }

    /**
     * Check if the Client Certificate P12 File exists.
     *
     * @return bool True if the file exists, false otherwise
     */
    public function checkClientCertificateP12FileExists(): bool
    {
        return $this->checkFileExists(self::CLIENT_CERT_P12_FILE);
    }

    /**
     * Get the path to the Client Certificate P12 File.
     *
     * @return string The full path to the P12 file
     * @throws RuntimeException If no P12 file has been stored
     */
    public function getClientCertificateP12FilePath(): string
    {
        return $this->getFilePath(self::CLIENT_CERT_P12_FILE);
    }

    /**
     * Store the uploaded Client Certificate Private Key File.
     *
     * @param UploadedFile $file The uploaded private key file
     * @throws RuntimeException If there's an error storing the file
     */
    public function storeClientCertificatePrivateKeyFile(UploadedFile $file): void
    {
        $this->storeUploadedFile($file, self::CLIENT_CERT_PRIVATEKEY_FILE);
    }

    /**
     * Check if the Client Certificate Private Key File exists.
     *
     * @return bool True if the file exists, false otherwise
     */
    public function checkClientCertificatePrivateKeyFileExists(): bool
    {
        return $this->checkFileExists(self::CLIENT_CERT_PRIVATEKEY_FILE);
    }

    /**
     * Get the path to the Client Certificate Private Key File.
     *
     * @return string The full path to the private key file
     * @throws RuntimeException If no private key file has been stored
     */
    public function getClientCertificatePrivateKeyFilePath(): string
    {
        return $this->getFilePath(self::CLIENT_CERT_PRIVATEKEY_FILE);
    }

    /**
     * Store the uploaded Client Certificate PEM File.
     *
     * @param UploadedFile $file The uploaded PEM file
     * @throws RuntimeException If there's an error storing the file
     */
    public function storeClientCertificatePEMFile(UploadedFile $file): void
    {
        $this->storeUploadedFile($file, self::CLIENT_CERT_PEM_FILE);
    }

    /**
     * Check if the Client Certificate PEM File exists.
     *
     * @return bool True if the file exists, false otherwise
     */
    public function checkClientCertificatePEMFileExists(): bool
    {
        return $this->checkFileExists(self::CLIENT_CERT_PEM_FILE);
    }

    /**
     * Get the path to the Client Certificate PEM File.
     *
     * @return string The full path to the PEM file
     * @throws RuntimeException If no PEM file has been stored
     */
    public function getClientCertificatePEMFilePath(): string
    {
        return $this->getFilePath(self::CLIENT_CERT_PEM_FILE);
    }

    /**
     * Store the uploaded Trust Anchor PEM File.
     *
     * @param UploadedFile $file The uploaded trust anchor file
     * @throws RuntimeException If there's an error storing the file
     */
    public function storeTrustAnchorPEMFile(UploadedFile $file): void
    {
        $this->storeUploadedFile($file, self::TRUST_ANCHOR_PEM_FILE);
    }

    /**
     * Check if the Trust Anchor PEM File exists.
     *
     * @return bool True if the file exists, false otherwise
     */
    public function checkTrustAnchorPEMFileExists(): bool
    {
        return $this->checkFileExists(self::TRUST_ANCHOR_PEM_FILE);
    }

    /**
     * Get the path to the Trust Anchor PEM File.
     *
     * @return string The full path to the trust anchor file
     * @throws RuntimeException If no trust anchor file has been stored
     */
    public function getTrustAnchorPEMFilePath(): string
    {
        return $this->getFilePath(self::TRUST_ANCHOR_PEM_FILE);
    }
}
