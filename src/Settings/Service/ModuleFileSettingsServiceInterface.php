<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Settings\Service;

interface ModuleFileSettingsServiceInterface
{
    public const CLIENT_CERT_P12_FILE = 'osctelecash_clientcertificatep12file';

    public const CLIENT_CERT_PRIVATEKEY_FILE = 'osctelecash_clientcertificateprivatekeyfile';

    public const CLIENT_CERT_PEM_FILE = 'osctelecash_clientcertificatepemfile';

    public const TRUST_ANCHOR_PEM_FILE = 'osctelecash_trustanchorpemfile';


    public function storeClientCertificateP12File(): void;

    public function checkClientCertificateP12FileExists(): bool;

    public function getClientCertificateP12FilePath(): string;

    public function storeClientCertificatePrivateKeyFile(): void;

    public function checkClientCertificatePrivateKeyFileExists(): bool;

    public function getClientCertificatePrivateKeyFilePath(): string;

    public function storeClientCertificatePEMFile(): void;

    public function checkClientCertificatePEMFileExists(): bool;

    public function getClientCertificatePEMFilePath(): string;

    public function storeTrustAnchorPEMFile(): void;

    public function checkTrustAnchorPEMFileExists(): bool;

    public function getTrustAnchorPEMFilePath(): string;
}
