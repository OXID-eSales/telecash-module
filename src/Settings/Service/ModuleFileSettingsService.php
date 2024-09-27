<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Settings\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidSolutionCatalysts\TeleCash\Core\Module;

/**
 * @extendable-class
 */
class ModuleFileSettingsService implements ModuleFileSettingsServiceInterface
{
    /**
     * store an uploaded Client Certificate P12 File to the server
     * @return void
     */
    public function storeClientCertificateP12File(): void
    {
        // TODO: Implement storeClientCertificateP12File() method.
    }

    /**
     * Checks if the Client Certificate P12 File is correct uploaded and still exists
     * @return bool
     */
    public function checkClientCertificateP12FileExists(): bool
    {
        // TODO: Implement checkClientCertificateP12FileExists() method.
        return false;
    }

    /**
     * Get the path from Client Certificate P12 File
     * @return string
     */
    public function getClientCertificateP12FilePath(): string
    {
        // TODO: Implement getClientCertificateP12FilePath() method.
        return "";
    }

    /**
     * store an uploaded Client Certificate Private Key to the server
     * @return void
     */
    public function storeClientCertificatePrivateKeyFile(): void
    {
        // TODO: Implement storeClientCertificatePrivateKeyFile() method.
    }

    /**
     * Checks if the Client Certificate Private Key File is correct uploaded and still exists
     * @return bool
     */
    public function checkClientCertificatePrivateKeyFileExists(): bool
    {
        // TODO: Implement checkClientCertificatePrivateKeyFileExists() method.
        return false;
    }

    /**
     * Get the path from Client Certificate Private Key File
     * @return string
     */
    public function getClientCertificatePrivateKeyFilePath(): string
    {
        // TODO: Implement getClientCertificatePrivateKeyFilePath() method.
        return "";
    }

    /**
     * store an uploaded Client Certificate PEM File to the server
     * @return void
     */
    public function storeClientCertificatePEMFile(): void
    {
        // TODO: Implement storeClientCertificatePEMFile() method.
    }

    /**
     * Checks if the Client Certificate PEM File is correct uploaded and still exists
     * @return bool
     */
    public function checkClientCertificatePEMFileExists(): bool
    {
        // TODO: Implement checkClientCertificatePEMFileExists() method.
        return false;
    }

    /**
     * Get the path from Client Certificate PEM File
     * @return string
     */
    public function getClientCertificatePEMFilePath(): string
    {
        // TODO: Implement getClientCertificatePEMFilePath() method.
        return "";
    }

    /**
     * store an uploaded Trust Anchor PEM File to the server
     * @return void
     */
    public function storeTrustAnchorPEMFile(): void
    {
        // TODO: Implement storeTrustAnchorPEMFile() method.
    }

    /**
     * Checks if the Trust Anchor PEM File is correct uploaded and still exists
     * @return bool
     */
    public function checkTrustAnchorPEMFileExists(): bool
    {
        // TODO: Implement checkTrustAnchorPEMFileExists() method.
        return false;
    }

    /**
     * Get the path from Trust Anchor PEM File
     * @return string
     */
    public function getTrustAnchorPEMFilePath(): string
    {
        // TODO: Implement getTrustAnchorPEMFilePath() method.
        return "";
    }
}
