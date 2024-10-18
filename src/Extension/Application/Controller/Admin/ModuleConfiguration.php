<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Extension\Application\Controller\Admin;

use Exception;
use OxidSolutionCatalysts\TeleCash\Core\Module;
use OxidSolutionCatalysts\TeleCash\Core\Service\RegistryService;
use OxidSolutionCatalysts\TeleCash\Core\Service\TranslateService;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleFileSettingsService;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleFileSettingsServiceInterface;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleSettingsServiceInterface;
use OxidSolutionCatalysts\TeleCash\Traits\ServiceContainer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ModuleConfiguration extends ModuleConfiguration_parent
{
    use ServiceContainer;

    protected RegistryService $registryService;
    protected ModuleFileSettingsServiceInterface $fileSettingsService;
    protected TranslateService $translateService;

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __construct()
    {
        parent::__construct();
        $this->fileSettingsService = $this->getServiceFromContainer(ModuleFileSettingsServiceInterface::class);
        $this->registryService = $this->getServiceFromContainer(RegistryService::class);
        $this->translateService = $this->getServiceFromContainer(TranslateService::class);
    }

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

        // collect setted options for template
        $aTeleCashFiles = [];
        foreach (ModuleFileSettingsService::TELECASH_GET_FILENAME_METHODS as $teleCashFile => $teleCashGetMethod) {
            $aTeleCashFiles[$teleCashFile] = $this->fileSettingsService->$teleCashGetMethod($teleCashFile);
        }
        $this->_aViewData['aTeleCashFiles'] = $aTeleCashFiles;

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
        $this->deleteTeleCashFiles();
    }


    /**
     * store the necessary telecash files
     *
     * @return void
     */
    protected function storeTeleCashFiles(): void
    {
        $config = $this->registryService->getConfig();
        $utilsView = $this->registryService->getUtilsView();

        foreach (ModuleFileSettingsService::TELECASH_STORE_METHODS as $teleCashFile => $teleCashStoreMethod) {
            /** @var array<string, mixed> $aFile */
            $aFile = $config->getUploadedFile($teleCashFile) ?? [];
            if (!empty($aFile['name'])) {
                $sTmpName = $aFile['tmp_name'] ?? '';
                $sName = $aFile['name'];
                $iError = $aFile['error'] ?? UPLOAD_ERR_NO_FILE;

                if ($iError === UPLOAD_ERR_OK && is_uploaded_file($sTmpName)) {
                    try {
                        $uploadedFile = new UploadedFile(
                            $sTmpName,
                            $sName,
                            $aFile['type'],
                            $aFile['error'],
                            true
                        );

                        $this->fileSettingsService->$teleCashStoreMethod($uploadedFile);
                        $translate = '';
                        $translate = $this->translateService->translateString('TELECASH_FILE_UPLOAD_SUCCESSFUL');
                        $utilsView->addErrorToDisplay(sprintf(
                            $translate,
                            $sName
                        ));
                    } catch (Exception $e) {
                        $translate = $this->translateService->translateString('TELECASH_FILE_UPLOAD_ERROR');
                        $utilsView->addErrorToDisplay(sprintf(
                            $translate,
                            $e->getMessage()
                        ));
                    }
                } else {
                    /** @var string $translate */
                    $translate = $this->translateService->translateString('TELECASH_FILE_UPLOAD_NOTVALID');
                    $utilsView->addErrorToDisplay(sprintf(
                        $translate,
                        $sName
                    ));
                }
            }
        }
    }

    /**
     * delete the unnecessary telecash files
     *
     * @return void
     */
    protected function deleteTeleCashFiles(): void
    {
        $request = $this->registryService->getRequest();

        foreach (ModuleFileSettingsService::TELECASH_DELETE_METHODS as $teleCashFile => $teleCashDeleteMethod) {
            if ($request->getRequestParameter($teleCashFile . '_delete')) {
                $this->fileSettingsService->$teleCashDeleteMethod();
            }
        }
    }
}
