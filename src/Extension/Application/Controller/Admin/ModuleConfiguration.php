<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Extension\Application\Controller\Admin;

use Exception;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\TeleCash\Core\Module;
use OxidSolutionCatalysts\TeleCash\Core\Service\RegistryService;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleFileSettingsService;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleFileSettingsServiceInterface;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleSettingsServiceInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ModuleConfiguration extends ModuleConfiguration_parent
{
    /**
     * OXID-Core
     * @inheritDoc
     * @return string
     */
    public function render()
    {
        $result = parent::render();

        $this->_aViewData['sTeleCashModuleId'] = Module::MODULE_ID;
        $this->_aViewData['sTeleCashVarGroupAPI'] = ModuleSettingsServiceInterface::MODULE_CONFIG_API_VARGROUP;
        $this->_aViewData['sTeleCashOptionTrigger'] = ModuleSettingsServiceInterface::CLIENT_CERT_PRIVATEKEY_PASSWORD;
        $this->_aViewData['aTeleCashFiles'] = array_keys(ModuleFileSettingsService::TELECASH_UPLOADS);

        return $result;
    }

    /**
     * OXID-Core
     * @inheritDoc
     * @return void
     */
    public function saveConfVars()
    {
        parent::saveConfVars();
        $this->storeTeleCashFiles();
    }


    /**
     * store the necessary telecash files
     * @inheritDoc
     * @return void
     */
    protected function storeTeleCashFiles(): void
    {
        $fileSettingsService = $this->getService(ModuleFileSettingsServiceInterface::class);
        $registryService = $this->getService(RegistryService::class);

        $lang = $registryService->getLang();
        $config = $registryService->getConfig();
        $utilsView = $registryService->getUtilsView();

        foreach (ModuleFileSettingsService::TELECASH_UPLOADS as $teleCashFile => $teleCashUploadMethod) {
            $aFile = $config->getUploadedFile($teleCashFile);
            if ($aFile !== null && !empty($aFile['name'])) {
                $sTmpName = $aFile['tmp_name'] ?: '';
                $sName = $aFile['name'];
                $iError = $aFile['error'] ?: UPLOAD_ERR_NO_FILE;

                if ($iError === UPLOAD_ERR_OK && is_uploaded_file($sTmpName)) {
                    try {
                        $uploadedFile = new UploadedFile(
                            $sTmpName,
                            $sName,
                            $aFile['type'],
                            $aFile['error'],
                            true
                        );

                        $fileSettingsService->$teleCashUploadMethod($uploadedFile);
                        /** @var string $translate */
                        $translate = $lang->translateString('TELECASH_FILE_UPLOAD_SUCCESSFUL');
                        $utilsView->addErrorToDisplay(sprintf(
                            $translate,
                            $sName
                        ));
                    } catch (Exception $e) {
                        /** @var string $translate */
                        $translate = $lang->translateString('TELECASH_FILE_UPLOAD_ERROR');
                        $utilsView->addErrorToDisplay(sprintf(
                            $translate,
                            $e->getMessage()
                        ));
                    }
                } else {
                    /** @var string $translate */
                    $translate = $lang->translateString('TELECASH_FILE_UPLOAD_NOTVALID');
                    $utilsView->addErrorToDisplay(sprintf(
                        $translate,
                        $sName
                    ));
                }
            }
        }
    }
}
