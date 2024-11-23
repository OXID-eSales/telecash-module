<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\Settings\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingService;
use OxidSolutionCatalysts\TeleCash\Core\Module;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleFileSettingsServiceInterface;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleSettingsService;
use OxidSolutionCatalysts\TeleCash\Settings\Service\ModuleSettingsServiceInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\String\UnicodeString;

/**
 * Test Suite for ModuleSettingsService
 *
 * This test class verifies all functionality of the ModuleSettingsService, including:
 * - API mode handling
 * - Credential management
 * - Certificate configuration
 * - Configuration validation
 *
 * Each test method uses mocked dependencies to isolate the testing scope
 * and ensure reliable test results.
 */
#[CoversClass(ModuleSettingsService::class)]
final class ModuleSettingsTest extends TestCase
{
    /**
     * Tests the validation of complete TeleCash backend configuration
     *
     * Verifies that the isValid method correctly evaluates:
     * - Presence of all required credentials
     * - Existence of necessary certificate files
     * - Completeness of configuration
     *
     * @return void
     */
    public function testIsValidBackendConfiguration(): void
    {
        // Create mocks for dependencies
        $moduleSettingService = $this->createPartialMock(ModuleSettingService::class, ['getString']);
        $fileSettingsService = $this->createMock(ModuleFileSettingsServiceInterface::class);

        // Setup valid configuration case
        $moduleSettingService->method('getString')->willReturnMap([
            [
                ModuleSettingsServiceInterface::STORE_ID,
                Module::MODULE_ID,
                new UnicodeString('valid-store')
            ],
            [
                ModuleSettingsServiceInterface::BASIC_AUTH_PASSWORD,
                Module::MODULE_ID,
                new UnicodeString('valid-auth')
            ],
            [
                ModuleSettingsServiceInterface::CLIENT_CERT_INSTALL_PASSWORD,
                Module::MODULE_ID,
                new UnicodeString('valid-cert')
            ],
            [
                ModuleSettingsServiceInterface::CLIENT_CERT_PRIVATEKEY_PASSWORD,
                Module::MODULE_ID,
                new UnicodeString('valid-key')
            ]
        ]);

        $fileSettingsService->method('checkClientCertificateP12FileExists')->willReturn(true);
        $fileSettingsService->method('checkClientCertificatePrivateKeyFileExists')->willReturn(true);
        $fileSettingsService->method('checkTrustAnchorPEMFileExists')->willReturn(true);

        $sut = new ModuleSettingsService($moduleSettingService, $fileSettingsService);

        // Test valid configuration
        $this->assertTrue(
            $sut->isValidBackendConfiguration(),
            'Configuration should be valid when all required settings are present'
        );

        // Test various invalid configurations
        $invalidCases = [
            'missing_store_id' => [
                'settings' => [
                    [
                        ModuleSettingsServiceInterface::STORE_ID,
                        Module::MODULE_ID,
                        new UnicodeString('')
                    ],
                    [
                        ModuleSettingsServiceInterface::BASIC_AUTH_PASSWORD,
                        Module::MODULE_ID,
                        new UnicodeString('valid-auth')],
                    [
                        ModuleSettingsServiceInterface::CLIENT_CERT_INSTALL_PASSWORD,
                        Module::MODULE_ID,
                        new UnicodeString('valid-cert')],
                    [
                        ModuleSettingsServiceInterface::CLIENT_CERT_PRIVATEKEY_PASSWORD,
                        Module::MODULE_ID,
                        new UnicodeString('valid-key')
                    ]
                ],
                'files_exist' => [true, true, true],
                'message' => 'Configuration should be invalid with missing store ID'
            ],
            'missing_auth_password' => [
                'settings' => [
                    [
                        ModuleSettingsServiceInterface::STORE_ID,
                        Module::MODULE_ID,
                        new UnicodeString('valid-store')
                    ],
                    [
                        ModuleSettingsServiceInterface::BASIC_AUTH_PASSWORD,
                        Module::MODULE_ID,
                        new UnicodeString('')
                    ],
                    [
                        ModuleSettingsServiceInterface::CLIENT_CERT_INSTALL_PASSWORD,
                        Module::MODULE_ID,
                        new UnicodeString('valid-cert')
                    ],
                    [
                        ModuleSettingsServiceInterface::CLIENT_CERT_PRIVATEKEY_PASSWORD,
                        Module::MODULE_ID,
                        new UnicodeString('valid-key')
                    ]
                ],
                'files_exist' => [true, true, true],
                'message' => 'Configuration should be invalid with missing auth password'
            ],
            'missing_cert_files' => [
                'settings' => [
                    [
                        ModuleSettingsServiceInterface::STORE_ID,
                        Module::MODULE_ID,
                        new UnicodeString('valid-store')
                    ],
                    [
                        ModuleSettingsServiceInterface::BASIC_AUTH_PASSWORD,
                        Module::MODULE_ID,
                        new UnicodeString('valid-auth')
                    ],
                    [
                        ModuleSettingsServiceInterface::CLIENT_CERT_INSTALL_PASSWORD,
                        Module::MODULE_ID,
                        new UnicodeString('valid-cert')
                    ],
                    [
                        ModuleSettingsServiceInterface::CLIENT_CERT_PRIVATEKEY_PASSWORD,
                        Module::MODULE_ID,
                        new UnicodeString('valid-key')
                    ]
                ],
                'files_exist' => [false, false, false],
                'message' => 'Configuration should be invalid with missing certificate files'
            ]
        ];

        foreach ($invalidCases as $caseName => $testCase) {
            // Create fresh mocks for each invalid case
            $invalidSettingService = $this->createPartialMock(ModuleSettingService::class, ['getString']);
            $invalidFileService = $this->createMock(ModuleFileSettingsServiceInterface::class);

            // Configure mocks with test case data
            $invalidSettingService->method('getString')->willReturnMap($testCase['settings']);
            $invalidFileService->method('checkClientCertificateP12FileExists')
                ->willReturn($testCase['files_exist'][0]);
            $invalidFileService->method('checkClientCertificatePrivateKeyFileExists')
                ->willReturn($testCase['files_exist'][1]);
            $invalidFileService->method('checkTrustAnchorPEMFileExists')
                ->willReturn($testCase['files_exist'][2]);

            $sut = new ModuleSettingsService($invalidSettingService, $invalidFileService);

            $this->assertFalse(
                $sut->isValidBackendConfiguration(),
                sprintf('[%s] %s', $caseName, $testCase['message'])
            );
        }
    }

    /**
     * Tests the validation of frontend TeleCash configuration
     *
     * Verifies that the isValidFrontendConfiguration method correctly evaluates:
     * - Presence of required store ID
     * - Presence of shared secret
     * - Various combinations of valid and invalid configurations
     *
     * @return void
     * @throws Exception
     */
    public function testIsValidFrontendConfiguration(): void
    {
        // Create mocks for dependencies
        $moduleSettingService = $this->createPartialMock(ModuleSettingService::class, ['getString']);
        $fileSettingsService = $this->createMock(ModuleFileSettingsServiceInterface::class);

        // Setup valid configuration case
        $moduleSettingService->method('getString')->willReturnMap([
            [
                ModuleSettingsServiceInterface::STORE_ID,
                Module::MODULE_ID,
                new UnicodeString('valid-store')
            ],
            [
                ModuleSettingsServiceInterface::SHARED_SECRET,
                Module::MODULE_ID,
                new UnicodeString('valid-secret')
            ]
        ]);

        $sut = new ModuleSettingsService($moduleSettingService, $fileSettingsService);

        // Test valid configuration
        $this->assertTrue(
            $sut->isValidFrontendConfiguration(),
            'Configuration should be valid when all required frontend settings are present'
        );

        // Test various invalid configurations
        $invalidCases = [
            'missing_store_id' => [
                'settings' => [
                    [
                        ModuleSettingsServiceInterface::STORE_ID,
                        Module::MODULE_ID,
                        new UnicodeString('')
                    ],
                    [
                        ModuleSettingsServiceInterface::SHARED_SECRET,
                        Module::MODULE_ID,
                        new UnicodeString('valid-secret')
                    ]
                ],
                'message' => 'Configuration should be invalid with missing store ID'
            ],
            'missing_shared_secret' => [
                'settings' => [
                    [
                        ModuleSettingsServiceInterface::STORE_ID,
                        Module::MODULE_ID,
                        new UnicodeString('valid-store')
                    ],
                    [
                        ModuleSettingsServiceInterface::SHARED_SECRET,
                        Module::MODULE_ID,
                        new UnicodeString('')
                    ]
                ],
                'message' => 'Configuration should be invalid with missing shared secret'
            ],
            'all_empty' => [
                'settings' => [
                    [
                        ModuleSettingsServiceInterface::STORE_ID,
                        Module::MODULE_ID,
                        new UnicodeString('')
                    ],
                    [
                        ModuleSettingsServiceInterface::SHARED_SECRET,
                        Module::MODULE_ID,
                        new UnicodeString('')
                    ]
                ],
                'message' => 'Configuration should be invalid with all settings empty'
            ]
        ];

        foreach ($invalidCases as $caseName => $testCase) {
            // Create fresh mocks for each invalid case
            $invalidSettingService = $this->createPartialMock(ModuleSettingService::class, ['getString']);
            $invalidFileService = $this->createMock(ModuleFileSettingsServiceInterface::class);

            // Configure mock with test case data
            $invalidSettingService->method('getString')->willReturnMap($testCase['settings']);

            $sut = new ModuleSettingsService($invalidSettingService, $invalidFileService);

            $this->assertFalse(
                $sut->isValidFrontendConfiguration(),
                sprintf('[%s] %s', $caseName, $testCase['message'])
            );
        }
    }

    /**
     * Tests the retrieval of API mode settings
     *
     * Verifies that getApiMode:
     * 1. Returns the correct stored API mode
     * 2. Defaults to API_MODE_LIVE when no valid value is stored
     * 3. Properly handles different input values
     *
     * @dataProvider getApiModeDataProvider
     *
     * @param string $storedValue The value stored in the settings
     * @param string $expectedMode The expected API mode
     * @throws Exception
     */
    public function testGetApiMode(string $storedValue, string $expectedMode): void
    {
        // Create and configure mocks
        $moduleSettingService = $this->createPartialMock(ModuleSettingService::class, ['getString']);
        $fileSettingsService = $this->createMock(ModuleFileSettingsServiceInterface::class);

        $moduleSettingService->method('getString')->willReturnMap([
            [ModuleSettingsServiceInterface::API_MODE, Module::MODULE_ID, new UnicodeString($storedValue)]
        ]);

        $sut = new ModuleSettingsService($moduleSettingService, $fileSettingsService);

        $this->assertSame(
            $expectedMode,
            $sut->getApiMode(),
            sprintf('API mode should be "%s" when stored value is "%s"', $expectedMode, $storedValue)
        );
    }

    /**
     * Provides test cases for API mode retrieval
     *
     * Test cases cover:
     * - Empty value (should default to live mode)
     * - Invalid value (should default to live mode)
     * - Valid live mode setting
     * - Valid sandbox mode setting
     *
     * @return array<string, array<string, string>> Test cases for API mode testing
     */
    public static function getApiModeDataProvider(): array
    {
        return [
            'empty_value' => [
                'storedValue' => '',
                'expectedMode' => ModuleSettingsServiceInterface::API_MODE_LIVE
            ],
            'invalid_value' => [
                'storedValue' => 'invalid_mode',
                'expectedMode' => ModuleSettingsServiceInterface::API_MODE_LIVE
            ],
            'live_mode' => [
                'storedValue' => ModuleSettingsServiceInterface::API_MODE_LIVE,
                'expectedMode' => ModuleSettingsServiceInterface::API_MODE_LIVE
            ],
            'sandbox_mode' => [
                'storedValue' => ModuleSettingsServiceInterface::API_MODE_SANDBOX,
                'expectedMode' => ModuleSettingsServiceInterface::API_MODE_SANDBOX
            ]
        ];
    }

    /**
     * Tests the API mode live status check
     *
     * Verifies that isLiveApiMode:
     * 1. Correctly identifies sandbox mode
     * 2. Correctly identifies live mode
     *
     * @dataProvider isLiveApiModeDataProvider
     *
     * @param string $storedMode The API mode stored in settings
     * @param bool $expectedResult Whether the mode should be considered live
     */
    public function testIsLiveApiMode(string $storedMode, bool $expectedResult): void
    {
        // Create and configure mocks
        $moduleSettingService = $this->createPartialMock(ModuleSettingService::class, ['getString']);
        $fileSettingsService = $this->createMock(ModuleFileSettingsServiceInterface::class);

        $moduleSettingService->method('getString')->willReturnMap([
            [ModuleSettingsServiceInterface::API_MODE, Module::MODULE_ID, new UnicodeString($storedMode)]
        ]);

        $sut = new ModuleSettingsService($moduleSettingService, $fileSettingsService);

        $this->assertSame(
            $expectedResult,
            $sut->isLiveApiMode(),
            sprintf(
                'isLiveApiMode should return %s when mode is %s',
                $expectedResult ? 'true' : 'false',
                $storedMode
            )
        );
    }

    /**
     * Provides test cases for live API mode checking
     *
     * Test cases verify:
     * - Live mode identification
     * - Sandbox mode identification
     *
     * @return array<string, array<string, mixed>> Test cases for live mode checking
     */
    public static function isLiveApiModeDataProvider(): array
    {
        return [
            'live_mode' => [
                'storedMode' => ModuleSettingsServiceInterface::API_MODE_LIVE,
                'expectedResult' => true
            ],
            'sandbox_mode' => [
                'storedMode' => ModuleSettingsServiceInterface::API_MODE_SANDBOX,
                'expectedResult' => false
            ]
        ];
    }

    /**
     * Tests the saving of API mode settings
     *
     * Verifies that saveApiMode:
     * 1. Correctly passes the mode to the storage service
     * 2. Uses the correct module identifier
     */
    public function testSaveApiMode(): void
    {
        $testMode = 'test_mode';

        // Create mocks with expectations
        $moduleSettingService = $this->createPartialMock(ModuleSettingService::class, ['saveString']);
        $fileSettingsService = $this->createMock(ModuleFileSettingsServiceInterface::class);

        $moduleSettingService->expects($this->once())
            ->method('saveString')
            ->with(
                ModuleSettingsServiceInterface::API_MODE,
                $testMode,
                Module::MODULE_ID
            );

        $sut = new ModuleSettingsService($moduleSettingService, $fileSettingsService);
        $sut->saveApiMode($testMode);
    }

    /**
     * Tests the retrieval of Store ID
     *
     * Verifies that getStoreId:
     * 1. Returns the correct stored value
     * 2. Properly handles type conversion
     * 3. Returns empty string for null values
     */
    public function testGetStoreId(): void
    {
        $expectedValue = 'test_store_123';

        // Create and configure mocks
        $moduleSettingService = $this->createPartialMock(ModuleSettingService::class, ['getString']);
        $fileSettingsService = $this->createMock(ModuleFileSettingsServiceInterface::class);

        $moduleSettingService->method('getString')
            ->with(ModuleSettingsServiceInterface::STORE_ID, Module::MODULE_ID)
            ->willReturn(new UnicodeString($expectedValue));

        $sut = new ModuleSettingsService($moduleSettingService, $fileSettingsService);

        $this->assertSame(
            $expectedValue,
            $sut->getStoreId(),
            'getStoreId should return the exact stored value'
        );
    }

    /**
     * Tests the saving of Store ID
     *
     * Verifies that saveStoreId:
     * 1. Correctly passes the value to the storage service
     * 2. Uses the correct module identifier
     * 3. Maintains data integrity
     */
    public function testSaveStoreId(): void
    {
        $testValue = 'new_store_123';

        // Create mocks with expectations
        $moduleSettingService = $this->createPartialMock(ModuleSettingService::class, ['saveString']);
        $fileSettingsService = $this->createMock(ModuleFileSettingsServiceInterface::class);

        $moduleSettingService->expects($this->once())
            ->method('saveString')
            ->with(
                ModuleSettingsServiceInterface::STORE_ID,
                $testValue,
                Module::MODULE_ID
            );

        $sut = new ModuleSettingsService($moduleSettingService, $fileSettingsService);
        $sut->saveStoreId($testValue);
    }

    /**
     * Tests the retrieval of Shared Secret
     *
     * Verifies that getSharedSecret:
     * 1. Returns the correct shared secret value
     * 2. Properly handles type conversion
     * 3. Returns empty string for null values
     */
    public function testGetSharedSecret(): void
    {
        $expectedValue = 'test_shared_secret';

        // Create and configure mocks
        $moduleSettingService = $this->createPartialMock(ModuleSettingService::class, ['getString']);
        $fileSettingsService = $this->createMock(ModuleFileSettingsServiceInterface::class);

        $moduleSettingService->method('getString')
            ->with(ModuleSettingsServiceInterface::SHARED_SECRET, Module::MODULE_ID)
            ->willReturn(new UnicodeString($expectedValue));

        $sut = new ModuleSettingsService($moduleSettingService, $fileSettingsService);

        $this->assertSame(
            $expectedValue,
            $sut->getSharedSecret(),
            'getStoreId should return the exact stored value'
        );
    }

    /**
     * Tests the saving of Shared Secret
     *
     * Verifies that saveSharedSecret:
     * 1. Correctly passes the value to the storage service
     * 2. Uses the correct module identifier
     * 3. Maintains data integrity
     */
    public function testSaveSharedSecret(): void
    {
        $testValue = 'new_shared_secret';

        // Create mocks with expectations
        $moduleSettingService = $this->createPartialMock(ModuleSettingService::class, ['saveString']);
        $fileSettingsService = $this->createMock(ModuleFileSettingsServiceInterface::class);

        $moduleSettingService->expects($this->once())
            ->method('saveString')
            ->with(
                ModuleSettingsServiceInterface::SHARED_SECRET,
                $testValue,
                Module::MODULE_ID
            );

        $sut = new ModuleSettingsService($moduleSettingService, $fileSettingsService);
        $sut->saveSharedSecret($testValue);
    }

    /**
     * Tests the retrieval of User ID
     *
     * Verifies that getUserId:
     * 1. Returns the correct stored value
     * 2. Properly handles type conversion
     * 3. Returns empty string for null values
     */
    public function testGetUserId(): void
    {
        $expectedValue = 'test_user_456';

        $moduleSettingService = $this->createPartialMock(ModuleSettingService::class, ['getString']);
        $fileSettingsService = $this->createMock(ModuleFileSettingsServiceInterface::class);

        $moduleSettingService->method('getString')
            ->with(ModuleSettingsServiceInterface::USER_ID, Module::MODULE_ID)
            ->willReturn(new UnicodeString($expectedValue));

        $sut = new ModuleSettingsService($moduleSettingService, $fileSettingsService);

        $this->assertSame(
            $expectedValue,
            $sut->getUserId(),
            'getUserId should return the exact stored value'
        );
    }

    /**
     * Tests the saving of User ID
     *
     * Verifies that saveUserId:
     * 1. Correctly passes the value to the storage service
     * 2. Uses the correct module identifier
     * 3. Maintains data integrity
     */
    public function testSaveUserId(): void
    {
        $testValue = 'new_user_456';

        $moduleSettingService = $this->createPartialMock(ModuleSettingService::class, ['saveString']);
        $fileSettingsService = $this->createMock(ModuleFileSettingsServiceInterface::class);

        $moduleSettingService->expects($this->once())
            ->method('saveString')
            ->with(
                ModuleSettingsServiceInterface::USER_ID,
                $testValue,
                Module::MODULE_ID
            );

        $sut = new ModuleSettingsService($moduleSettingService, $fileSettingsService);
        $sut->saveUserId($testValue);
    }

    /**
     * Tests the retrieval of Basic Auth Password
     *
     * Verifies that getBasicAuthPassword:
     * 1. Returns the correct stored password
     * 2. Properly handles sensitive data
     * 3. Returns empty string for null values
     */
    public function testGetBasicAuthPassword(): void
    {
        $expectedValue = 'secure_password_789';

        $moduleSettingService = $this->createPartialMock(ModuleSettingService::class, ['getString']);
        $fileSettingsService = $this->createMock(ModuleFileSettingsServiceInterface::class);

        $moduleSettingService->method('getString')
            ->with(ModuleSettingsServiceInterface::BASIC_AUTH_PASSWORD, Module::MODULE_ID)
            ->willReturn(new UnicodeString($expectedValue));

        $sut = new ModuleSettingsService($moduleSettingService, $fileSettingsService);

        $this->assertSame(
            $expectedValue,
            $sut->getBasicAuthPassword(),
            'getBasicAuthPassword should return the exact stored value'
        );
    }

    /**
     * Tests the saving of Basic Auth Password
     *
     * Verifies that saveBasicAuthPassword:
     * 1. Correctly passes the value to the storage service
     * 2. Uses the correct module identifier
     * 3. Handles sensitive data appropriately
     */
    public function testSaveBasicAuthPassword(): void
    {
        $testValue = 'new_secure_password_789';

        $moduleSettingService = $this->createPartialMock(ModuleSettingService::class, ['saveString']);
        $fileSettingsService = $this->createMock(ModuleFileSettingsServiceInterface::class);

        $moduleSettingService->expects($this->once())
            ->method('saveString')
            ->with(
                ModuleSettingsServiceInterface::BASIC_AUTH_PASSWORD,
                $testValue,
                Module::MODULE_ID
            );

        $sut = new ModuleSettingsService($moduleSettingService, $fileSettingsService);
        $sut->saveBasicAuthPassword($testValue);
    }

    /**
     * Tests the retrieval of Client Certificate Installation Password
     *
     * Verifies that getClientCertificateInstallationPassword:
     * 1. Returns the correct stored certificate password
     * 2. Properly handles sensitive data
     * 3. Returns empty string for null values
     * 4. Maintains security of certificate credentials
     */
    public function testGetClientCertificateInstallationPassword(): void
    {
        $expectedValue = 'cert_install_password_123';

        $moduleSettingService = $this->createPartialMock(ModuleSettingService::class, ['getString']);
        $fileSettingsService = $this->createMock(ModuleFileSettingsServiceInterface::class);

        $moduleSettingService->method('getString')
            ->with(ModuleSettingsServiceInterface::CLIENT_CERT_INSTALL_PASSWORD, Module::MODULE_ID)
            ->willReturn(new UnicodeString($expectedValue));

        $sut = new ModuleSettingsService($moduleSettingService, $fileSettingsService);

        $this->assertSame(
            $expectedValue,
            $sut->getClientCertificateInstallationPassword(),
            'getClientCertificateInstallationPassword should return the exact stored value'
        );
    }

    /**
     * Tests the saving of Client Certificate Installation Password
     *
     * Verifies that saveClientCertificateInstallationPassword:
     * 1. Correctly passes the value to the storage service
     * 2. Uses the correct module identifier
     * 3. Handles sensitive certificate data appropriately
     * 4. Maintains data integrity and security
     */
    public function testSaveClientCertificateInstallationPassword(): void
    {
        $testValue = 'new_cert_install_password_123';

        $moduleSettingService = $this->createPartialMock(ModuleSettingService::class, ['saveString']);
        $fileSettingsService = $this->createMock(ModuleFileSettingsServiceInterface::class);

        $moduleSettingService->expects($this->once())
            ->method('saveString')
            ->with(
                ModuleSettingsServiceInterface::CLIENT_CERT_INSTALL_PASSWORD,
                $testValue,
                Module::MODULE_ID
            );

        $sut = new ModuleSettingsService($moduleSettingService, $fileSettingsService);
        $sut->saveClientCertificateInstallationPassword($testValue);
    }

    /**
     * Tests the retrieval of Client Certificate Private Key Password
     *
     * Verifies that getClientCertificatePrivateKeyPassword:
     * 1. Returns the correct stored private key password
     * 2. Properly handles sensitive data
     * 3. Returns empty string for null values
     * 4. Maintains security of private key credentials
     */
    public function testGetClientCertificatePrivateKeyPassword(): void
    {
        $expectedValue = 'private_key_password_456';

        $moduleSettingService = $this->createPartialMock(ModuleSettingService::class, ['getString']);
        $fileSettingsService = $this->createMock(ModuleFileSettingsServiceInterface::class);

        $moduleSettingService->method('getString')
            ->with(ModuleSettingsServiceInterface::CLIENT_CERT_PRIVATEKEY_PASSWORD, Module::MODULE_ID)
            ->willReturn(new UnicodeString($expectedValue));

        $sut = new ModuleSettingsService($moduleSettingService, $fileSettingsService);

        $this->assertSame(
            $expectedValue,
            $sut->getClientCertificatePrivateKeyPassword(),
            'getClientCertificatePrivateKeyPassword should return the exact stored value'
        );
    }

    /**
     * Tests the saving of Client Certificate Private Key Password
     *
     * Verifies that saveClientCertificatePrivateKeyPassword:
     * 1. Correctly passes the value to the storage service
     * 2. Uses the correct module identifier
     * 3. Handles sensitive private key data appropriately
     * 4. Maintains data integrity and security
     * 5. Properly stores the credential in the module settings
     */
    public function testSaveClientCertificatePrivateKeyPassword(): void
    {
        $testValue = 'new_private_key_password_456';

        $moduleSettingService = $this->createPartialMock(ModuleSettingService::class, ['saveString']);
        $fileSettingsService = $this->createMock(ModuleFileSettingsServiceInterface::class);

        $moduleSettingService->expects($this->once())
            ->method('saveString')
            ->with(
                ModuleSettingsServiceInterface::CLIENT_CERT_PRIVATEKEY_PASSWORD,
                $testValue,
                Module::MODULE_ID
            );

        $sut = new ModuleSettingsService($moduleSettingService, $fileSettingsService);
        $sut->saveClientCertificatePrivateKeyPassword($testValue);
    }

    /**
     * Tests handling of empty or null values
     *
     * This test suite verifies that all getter methods properly handle
     * empty or null values from the storage service. It ensures that:
     * 1. Empty strings are handled gracefully
     * 2. Null values are converted to empty strings
     * 3. The system remains stable with missing data
     *
     * @dataProvider emptyValueMethodsDataProvider
     *
     * @param string $methodName The getter method to test
     * @throws Exception
     */
    public function testEmptyValueHandling(string $methodName): void
    {
        $moduleSettingService = $this->createPartialMock(ModuleSettingService::class, ['getString']);
        $fileSettingsService = $this->createMock(ModuleFileSettingsServiceInterface::class);

        // Configure mock to return empty string for any request
        $moduleSettingService->method('getString')
            ->willReturn(new UnicodeString(''));

        $sut = new ModuleSettingsService($moduleSettingService, $fileSettingsService);

        $this->assertSame(
            '',
            $sut->$methodName(),
            sprintf('Method %s should return empty string for empty/null values', $methodName)
        );
    }

    /**
     * Provides test cases for empty value handling
     *
     * Lists all getter methods that should be tested for empty value handling
     *
     * @return array<string, array<string>> List of methods to test
     */
    public static function emptyValueMethodsDataProvider(): array
    {
        return [
            'store_id' => ['getStoreId'],
            'user_id' => ['getUserId'],
            'basic_auth_password' => ['getBasicAuthPassword'],
            'cert_install_password' => ['getClientCertificateInstallationPassword'],
            'private_key_password' => ['getClientCertificatePrivateKeyPassword']
        ];
    }

    /**
     * Tests the retrieval of log level settings
     *
     * Verifies that getLogLevel:
     * 1. Returns the correct stored log level
     * 2. Defaults to LOG_LEVEL_ERROR when no valid value is stored
     * 3. Properly handles different input values
     *
     * @dataProvider getLogLevelDataProvider
     *
     * @param string $storedValue The value stored in the settings
     * @param string $expectedLevel The expected log level
     * @throws Exception
     */
    public function testGetLogLevel(string $storedValue, string $expectedLevel): void
    {
        // Create and configure mocks
        $moduleSettingService = $this->createPartialMock(ModuleSettingService::class, ['getString']);
        $fileSettingsService = $this->createMock(ModuleFileSettingsServiceInterface::class);

        $moduleSettingService->method('getString')->willReturnMap([
            [ModuleSettingsServiceInterface::LOG_LEVEL, Module::MODULE_ID, new UnicodeString($storedValue)]
        ]);

        $sut = new ModuleSettingsService($moduleSettingService, $fileSettingsService);

        $this->assertSame(
            $expectedLevel,
            $sut->getLogLevel(),
            sprintf('Log level should be "%s" when stored value is "%s"', $expectedLevel, $storedValue)
        );
    }

    /**
     * Provides test cases for log level retrieval
     *
     * Test cases cover:
     * - Empty value (should default to error level)
     * - Invalid value (should default to error level)
     * - All valid log levels
     *
     * @return array<string, array<string, string>> Test cases for log level testing
     */
    public static function getLogLevelDataProvider(): array
    {
        return [
            'empty_value' => [
                'storedValue' => '',
                'expectedLevel' => ModuleSettingsServiceInterface::LOG_LEVEL_ERROR
            ],
            'invalid_value' => [
                'storedValue' => 'invalid_level',
                'expectedLevel' => ModuleSettingsServiceInterface::LOG_LEVEL_ERROR
            ],
            'error_level' => [
                'storedValue' => ModuleSettingsServiceInterface::LOG_LEVEL_ERROR,
                'expectedLevel' => ModuleSettingsServiceInterface::LOG_LEVEL_ERROR
            ],
            'info_level' => [
                'storedValue' => ModuleSettingsServiceInterface::LOG_LEVEL_INFO,
                'expectedLevel' => ModuleSettingsServiceInterface::LOG_LEVEL_INFO
            ],
            'debug_level' => [
                'storedValue' => ModuleSettingsServiceInterface::LOG_LEVEL_DEBUG,
                'expectedLevel' => ModuleSettingsServiceInterface::LOG_LEVEL_DEBUG
            ]
        ];
    }

    /**
     * Tests the retrieval of Connect URL based on API mode
     *
     * Verifies that getConnectUrl:
     * 1. Returns the correct URL for live mode
     * 2. Returns the correct URL for sandbox mode
     * 3. Returns the correct default URL when mode is invalid
     *
     * @dataProvider getConnectUrlDataProvider
     *
     * @param string $apiMode The API mode to test
     * @param string $expectedUrl The expected Connect URL
     * @throws Exception
     */
    public function testGetConnectUrl(string $apiMode, string $expectedUrl): void
    {
        // Create and configure mocks
        $moduleSettingService = $this->createPartialMock(ModuleSettingService::class, ['getString']);
        $fileSettingsService = $this->createMock(ModuleFileSettingsServiceInterface::class);

        // Configure the mock to return our test API mode
        $moduleSettingService->method('getString')->willReturnMap([
            [ModuleSettingsServiceInterface::API_MODE, Module::MODULE_ID, new UnicodeString($apiMode)]
        ]);

        $sut = new ModuleSettingsService($moduleSettingService, $fileSettingsService);

        $this->assertSame(
            $expectedUrl,
            $sut->getConnectUrl(),
            sprintf('Connect URL should be "%s" when API mode is "%s"', $expectedUrl, $apiMode)
        );
    }

    /**
     * Provides test cases for Connect URL retrieval
     *
     * Test cases cover:
     * - Live mode URL
     * - Sandbox mode URL
     * - Default URL for invalid mode
     *
     * @return array<string, array<string, string>> Test cases for Connect URL testing
     */
    public static function getConnectUrlDataProvider(): array
    {
        return [
            'live_mode' => [
                'apiMode' => ModuleSettingsServiceInterface::API_MODE_LIVE,
                'expectedUrl' => ModuleSettingsServiceInterface::CONNECT_URLS[
                    ModuleSettingsServiceInterface::API_MODE_LIVE
                ]
            ],
            'sandbox_mode' => [
                'apiMode' => ModuleSettingsServiceInterface::API_MODE_SANDBOX,
                'expectedUrl' => ModuleSettingsServiceInterface::CONNECT_URLS[
                    ModuleSettingsServiceInterface::API_MODE_SANDBOX
                ]
            ],
            'invalid_mode' => [
                'apiMode' => 'invalid_mode',
                'expectedUrl' => ModuleSettingsServiceInterface::CONNECT_URLS[
                    ModuleSettingsServiceInterface::API_MODE_LIVE
                ]
            ]
        ];
    }

    /**
     * Tests the saving of log level settings
     *
     * Verifies that saveLogLevel:
     * 1. Correctly passes the level to the storage service
     * 2. Uses the correct module identifier
     * 3. Maintains data integrity
     */
    public function testSaveLogLevel(): void
    {
        $testLevel = ModuleSettingsServiceInterface::LOG_LEVEL_INFO;

        // Create mocks with expectations
        $moduleSettingService = $this->createPartialMock(ModuleSettingService::class, ['saveString']);
        $fileSettingsService = $this->createMock(ModuleFileSettingsServiceInterface::class);

        $moduleSettingService->expects($this->once())
            ->method('saveString')
            ->with(
                ModuleSettingsServiceInterface::LOG_LEVEL,
                $testLevel,
                Module::MODULE_ID
            );

        $sut = new ModuleSettingsService($moduleSettingService, $fileSettingsService);
        $sut->saveLogLevel($testLevel);
    }
}
