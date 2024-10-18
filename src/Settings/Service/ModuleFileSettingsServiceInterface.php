<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Settings\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface ModuleFileSettingsServiceInterface
{
    public const CLIENT_CERT_P12_FILE = 'osctelecash_clientcertificatep12file';

    public const CLIENT_CERT_PRIVATEKEY_FILE = 'osctelecash_clientcertificateprivatekeyfile';

    public const TRUST_ANCHOR_PEM_FILE = 'osctelecash_trustanchorpemfile';


    public function storeClientCertificateP12File(UploadedFile $file): void;

    public function checkClientCertificateP12FileExists(): bool;


    public function getClientCertificateP12FileName(): string;

    public function getClientCertificateP12FilePath(): string;

    public function deleteClientCertificateP12File(): bool;

    public function storeClientCertificatePrivateKeyFile(UploadedFile $file): void;

    public function checkClientCertificatePrivateKeyFileExists(): bool;

    public function getClientCertificatePrivateKeyFileName(): string;

    public function getClientCertificatePrivateKeyFilePath(): string;

    public function deleteClientCertificatePrivateKeyFile(): bool;

    public function storeTrustAnchorPEMFile(UploadedFile $file): void;

    public function checkTrustAnchorPEMFileExists(): bool;

    public function getTrustAnchorPEMFileName(): string;

    public function getTrustAnchorPEMFilePath(): string;

    public function deleteTrustAnchorPEMFile(): bool;
}
