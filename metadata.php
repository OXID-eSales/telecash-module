<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

use OxidEsales\Eshop\Application\Controller\Admin\ModuleConfiguration as oxModuleConfiguration;
use OxidEsales\Eshop\Application\Controller\Admin\OrderMain as oxOrderMain;
use OxidEsales\Eshop\Application\Controller\Admin\OrderOverview as oxOrderOverview;
use OxidEsales\Eshop\Application\Controller\Admin\PaymentMain as oxPaymentMain;
use OxidEsales\Eshop\Application\Controller\OrderController as oxOrderController;
use OxidEsales\Eshop\Application\Controller\PaymentController as oxPaymentController;
use OxidEsales\Eshop\Application\Model\Address as oxAddress;
use OxidEsales\Eshop\Application\Model\Country as oxCountry;
use OxidEsales\Eshop\Application\Model\Order as oxOrder;
use OxidEsales\Eshop\Application\Model\Payment as oxPayment;
use OxidEsales\Eshop\Application\Model\PaymentGateway as oxPaymentGateway;
use OxidEsales\Eshop\Application\Model\PaymentList as oxPaymentList;
use OxidEsales\Eshop\Application\Model\State as oxState;
use OxidEsales\Eshop\Application\Model\User as oxUser;
use OxidSolutionCatalysts\TeleCash\Application\Controller\Admin\AdminTeleCashJsonEndpoint;
use OxidSolutionCatalysts\TeleCash\Application\Controller\Admin\OrderTeleCash;
use OxidSolutionCatalysts\TeleCash\Application\Controller\FrontendTeleCashNotificationEndpoint;
use OxidSolutionCatalysts\TeleCash\Core\Module;
use OxidSolutionCatalysts\TeleCash\Extension\Application\Controller\Admin\ModuleConfiguration;
use OxidSolutionCatalysts\TeleCash\Extension\Application\Controller\Admin\OrderMain;
use OxidSolutionCatalysts\TeleCash\Extension\Application\Controller\Admin\OrderOverview;
use OxidSolutionCatalysts\TeleCash\Extension\Application\Controller\Admin\PaymentMain;
use OxidSolutionCatalysts\TeleCash\Extension\Application\Controller\OrderController;
use OxidSolutionCatalysts\TeleCash\Extension\Application\Controller\PaymentController;
use OxidSolutionCatalysts\TeleCash\Extension\Application\Model\Address;
use OxidSolutionCatalysts\TeleCash\Extension\Application\Model\Country;
use OxidSolutionCatalysts\TeleCash\Extension\Application\Model\Order;
use OxidSolutionCatalysts\TeleCash\Extension\Application\Model\Payment;
use OxidSolutionCatalysts\TeleCash\Extension\Application\Model\PaymentGateway;
use OxidSolutionCatalysts\TeleCash\Extension\Application\Model\PaymentList;
use OxidSolutionCatalysts\TeleCash\Extension\Application\Model\User;
use OxidSolutionCatalysts\TeleCash\Extension\Application\Model\State;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleFileSettingsServiceInterface;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleLanguageSettingsServiceInterface;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleSettingsServiceInterface;

/**
 * Metadata version
 */
$sMetadataVersion = '2.1';

/**
 * Module information
 */
$aModule = [
    'id'          => Module::MODULE_ID,
    'title'       => [
        'de' => 'Zahlungs-Module für Zahlungsdienstleisters Telecash',
        'en' => 'Payment-Module for Payment-Provider Telecash',
    ],
    'description' => [
        'de' => 'Dieses Modul ermöglicht die Integration des Zahlungsdienstleisters Telecash.',
        'en' => 'This module provides the integration of the payment provider Telecash.',
    ],
    'thumbnail'   => 'pictures/logo.png',
    'version'     => '1.0.0-rc.1',
    'author'      => 'OXID eSales AG',
    'url'         => '',
    'email'       => '',
    'controllers' => [
        // Admin
        'AdminTeleCashJsonEndpoint' => AdminTeleCashJsonEndpoint::class,
        'OrderTeleCash'             => OrderTeleCash::class,

        // Frontend
        'FrontendTeleCashNotificationEndpoint' => FrontendTeleCashNotificationEndpoint::class,
    ],
    'events' => [
        'onActivate' => '\OxidSolutionCatalysts\TeleCash\Core\ModuleEvents::onActivate',
        'onDeactivate' => '\OxidSolutionCatalysts\TeleCash\Core\ModuleEvents::onDeactivate'
    ],
    'extend' => [
        // Controller Admin
        oxModuleConfiguration::class => ModuleConfiguration::class,
        oxPaymentMain::class         => PaymentMain::class,
        oxOrderMain::class           => OrderMain::class,
        oxOrderOverview::class       => OrderOverview::class,
        // Controller Frontend
        oxOrderController::class     => OrderController::class,
        oxPaymentController::class   => PaymentController::class,
        // Models
        oxAddress::class             => Address::class,
        oxCountry::class             => Country::class,
        oxOrder::class               => Order::class,
        oxPayment::class             => Payment::class,
        oxPaymentGateway::class      => PaymentGateway::class,
        oxPaymentList::class         => PaymentList::class,
        oxUser::class                => User::class,
        oxState::class               => State::class,
    ],
    'settings' => [
        [
            'group'       => ModuleSettingsServiceInterface::MODULE_CONFIG_API_VARGROUP,
            'name'        => ModuleSettingsServiceInterface::API_MODE,
            'type'        => 'select',
            'constraints' => ModuleSettingsServiceInterface::API_MODE_SANDBOX . '|' . ModuleSettingsServiceInterface::API_MODE_LIVE,
            'value'       => ModuleSettingsServiceInterface::API_MODE_SANDBOX
        ],
        [
            'group' => ModuleSettingsServiceInterface::MODULE_CONFIG_API_VARGROUP,
            'name'  => ModuleSettingsServiceInterface::STORE_ID,
            'type'  => 'str',
            'value' => '',
        ],
        [
            'group' => ModuleSettingsServiceInterface::MODULE_CONFIG_API_FRONTEND_VARGROUP,
            'name'  => ModuleSettingsServiceInterface::SHARED_SECRET,
            'type'  => 'str',
            'value' => '',
        ],
        [
            'group' => ModuleSettingsServiceInterface::MODULE_CONFIG_API_BACKEND_VARGROUP,
            'name'  => ModuleSettingsServiceInterface::USER_ID,
            'type'  => 'str',
            'value' => '',
        ],
        [
            'group' => ModuleSettingsServiceInterface::MODULE_CONFIG_API_BACKEND_VARGROUP,
            'name'  => ModuleSettingsServiceInterface::BASIC_AUTH_PASSWORD,
            'type'  => 'str',
            'value' => '',
        ],
        [
            'group' => ModuleSettingsServiceInterface::MODULE_CONFIG_API_BACKEND_VARGROUP,
            'name'  => ModuleSettingsServiceInterface::CLIENT_CERT_INSTALL_PASSWORD,
            'type'  => 'str',
            'value' => '',
        ],
        [
            'group' => ModuleSettingsServiceInterface::MODULE_CONFIG_API_BACKEND_VARGROUP,
            'name'  => ModuleSettingsServiceInterface::CLIENT_CERT_PRIVATEKEY_PASSWORD,
            'type'  => 'str',
            'value' => '',
        ],
        [
            'group' => ModuleSettingsServiceInterface::MODULE_CONFIG_LANGUAGE,
            'name'  => ModuleLanguageSettingsServiceInterface::LANGUAGES,
            'type'  => 'aarr',
            'value' => [
                'de' => 'de_DE',
                'en' => 'en_US',
            ]
        ],
        [
            'group'       => ModuleSettingsServiceInterface::MODULE_CONFIG_DEBUG_VARGROUP,
            'name'        => ModuleSettingsServiceInterface::LOG_LEVEL,
            'type'        => 'select',
            'constraints' => ModuleSettingsServiceInterface::LOG_LEVEL_ERROR . '|' .
                ModuleSettingsServiceInterface::LOG_LEVEL_INFO. '|' .
                ModuleSettingsServiceInterface::LOG_LEVEL_DEBUG,
            'value'       => ModuleSettingsServiceInterface::LOG_LEVEL_ERROR
        ],
        // these options are hidden, so the group is null
        [
            'group' => null,
            'name'  => ModuleFileSettingsServiceInterface::CLIENT_CERT_P12_FILE,
            'type'  => 'str',
            'value' => '',
        ],
        [
            'group' => null,
            'name'  => ModuleFileSettingsServiceInterface::CLIENT_CERT_PRIVATEKEY_FILE,
            'type'  => 'str',
            'value' => '',
        ],
        [
            'group' => null,
            'name'  => ModuleFileSettingsServiceInterface::TRUST_ANCHOR_PEM_FILE,
            'type'  => 'str',
            'value' => '',
        ],
    ]
];
