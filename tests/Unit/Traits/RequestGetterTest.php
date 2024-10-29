<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\Traits\Traits;

use OxidEsales\Eshop\Core\Request;
use OxidSolutionCatalysts\TeleCash\Core\Service\RegistryService;
use OxidSolutionCatalysts\TeleCash\Tests\Unit\Traits\TestClasses\RequestGetterTestClass;
use PHPUnit\Framework\TestCase;

/**
 * Integration Test for RequestGetter Trait
 *
 * This test suite verifies the functionality of the RequestGetter trait which provides
 * type-safe methods for accessing request parameters in OXID controllers and models.
 * The trait wraps OXID's native request handling to ensure type safety and consistent
 * default values.
 *
 * Test Structure:
 * - Each test method verifies a specific type conversion (int, array, bool, string)
 * - Mock objects simulate OXID's Request and Registry services
 * - Test cases include valid inputs, edge cases, and type conversions
 * - Each test verifies both value correctness and type safety
 */
class RequestGetterTest extends TestCase
{
    private Request $request;
    private RegistryService $registryService;
    private RequestGetterTestClass $testClass;

    /**
     * Sets up the test environment before each test.
     *
     * Creates and configures:
     * - Request mock object
     * - RegistryService mock object
     * - Test class instance with RequestGetter trait
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create mocks
        $this->request = $this->createMock(Request::class);
        $this->registryService = $this->createMock(RegistryService::class);
        $this->registryService->method('getRequest')
            ->willReturn($this->request);

        // Initialize test class
        $this->testClass = new RequestGetterTestClass();
        $this->testClass->setRegistryService($this->registryService);
    }

    /**
     * Tests integer parameter retrieval functionality
     *
     * Verifies:
     * - Valid integers are returned unchanged
     * - Non-integer inputs return default value (0)
     * - Type safety is maintained (always returns int)
     *
     * Test cases:
     * - Valid integer (42 → 42)
     * - String integer ('42' → 0)
     * - Null (null → 0)
     * - Array ([] → 0)
     */
    public function testGetIntegerRequestData(): void
    {
        // Test cases for integer
        $testCases = [
            ['input' => 42, 'expected' => 42],
            ['input' => '42', 'expected' => 0],  // String should return default
            ['input' => null, 'expected' => 0],
            ['input' => [], 'expected' => 0],
        ];

        foreach ($testCases as $case) {
            // Arrange
            $this->request->expects($this->once())
                ->method('getRequestParameter')
                ->with('testKey', 0)
                ->willReturn($case['input']);

            // Act
            $result = $this->testClass->getIntegerRequestData('testKey');

            // Assert
            $this->assertSame($case['expected'], $result);
            $this->assertIsInt($result);

            // Reset mock
            $this->setUp();
        }
    }

    /**
     * Tests array parameter retrieval functionality
     *
     * Verifies:
     * - Valid arrays are returned unchanged
     * - Non-array inputs return empty array
     * - Array structure is preserved
     *
     * Test cases:
     * - Valid array (['test' => 'value'] → ['test' => 'value'])
     * - String ('not-an-array' → [])
     * - Null (null → [])
     * - Integer (42 → [])
     */
    public function testGetArrayRequestData(): void
    {
        // Test cases for array
        $testCases = [
            ['input' => ['test' => 'value'], 'expected' => ['test' => 'value']],
            ['input' => 'not-an-array', 'expected' => []],
            ['input' => null, 'expected' => []],
            ['input' => 42, 'expected' => []],
        ];

        foreach ($testCases as $case) {
            // Arrange
            $this->request->expects($this->once())
                ->method('getRequestParameter')
                ->with('testKey', [])
                ->willReturn($case['input']);

            // Act
            $result = $this->testClass->getArrayRequestData('testKey');

            // Assert
            $this->assertSame($case['expected'], $result);
            $this->assertIsArray($result);

            // Reset mock
            $this->setUp();
        }
    }

    /**
     * Tests boolean parameter retrieval functionality
     *
     * Verifies:
     * - Boolean values are handled correctly
     * - Various truthy/falsy values are converted properly
     * - Type safety is maintained (always returns bool)
     *
     * Test cases:
     * - Boolean (true/false → true/false)
     * - Integer (1/0 → true/false)
     * - String ('1'/'0' → true/false)
     * - Null (null → false)
     */
    public function testGetBoolRequestData(): void
    {
        // Test cases for boolean
        $testCases = [
            ['input' => true, 'expected' => true],
            ['input' => 1, 'expected' => true],
            ['input' => '1', 'expected' => true],
            ['input' => false, 'expected' => false],
            ['input' => 0, 'expected' => false],
            ['input' => '0', 'expected' => false],
            ['input' => null, 'expected' => false],
        ];

        foreach ($testCases as $case) {
            // Arrange
            $this->request->expects($this->once())
                ->method('getRequestParameter')
                ->with('testKey', null)
                ->willReturn($case['input']);

            // Act
            $result = $this->testClass->getBoolRequestData('testKey');

            // Assert
            $this->assertSame($case['expected'], $result);
            $this->assertIsBool($result);

            // Reset mock
            $this->setUp();
        }
    }

    /**
     * Tests escaped string parameter retrieval functionality
     *
     * Verifies:
     * - String values are properly escaped
     * - Non-string inputs return empty string
     * - Type safety is maintained (always returns string)
     *
     * Test cases:
     * - Valid string ('test string' → 'test string')
     * - Integer (42 → '')
     * - Null (null → '')
     * - Array ([] → '')
     */
    public function testGetStringRequestEscapedData(): void
    {
        // Test cases for escaped string
        $testCases = [
            ['input' => 'test string', 'expected' => 'test string'],
            ['input' => 42, 'expected' => ''],
            ['input' => null, 'expected' => ''],
            ['input' => [], 'expected' => ''],
        ];

        foreach ($testCases as $case) {
            // Arrange
            $this->request->expects($this->once())
                ->method('getRequestEscapedParameter')
                ->with('testKey', '')
                ->willReturn($case['input']);

            // Act
            $result = $this->testClass->getStringRequestEscapedData('testKey');

            // Assert
            $this->assertSame($case['expected'], $result);
            $this->assertIsString($result);

            // Reset mock
            $this->setUp();
        }
    }

    /**
     * Tests escaped array parameter retrieval functionality
     *
     * Verifies:
     * - Arrays are properly escaped
     * - Non-array inputs return empty array
     * - Array structure is preserved
     * - Type safety is maintained (always returns array)
     *
     * Test cases:
     * - Valid array (['test' => 'value'] → ['test' => 'value'])
     * - String ('not-an-array' → [])
     * - Null (null → [])
     * - Integer (42 → [])
     */
    public function testGetArrayRequestEscapedData(): void
    {
        // Test cases for escaped array
        $testCases = [
            ['input' => ['test' => 'value'], 'expected' => ['test' => 'value']],
            ['input' => 'not-an-array', 'expected' => []],
            ['input' => null, 'expected' => []],
            ['input' => 42, 'expected' => []],
        ];

        foreach ($testCases as $case) {
            // Arrange
            $this->request->expects($this->once())
                ->method('getRequestParameter')
                ->with('testKey', [])
                ->willReturn($case['input']);

            // Act
            $result = $this->testClass->getArrayRequestEscapedData('testKey');

            // Assert
            $this->assertSame($case['expected'], $result);
            $this->assertIsArray($result);

            // Reset mock
            $this->setUp();
        }
    }

    /**
     * Tests behavior when RegistryService is not available
     *
     * Verifies that all methods return their default values when:
     * - RegistryService is null
     * - Request object cannot be obtained
     *
     * Tests all methods:
     * - Integer returns 0
     * - Array returns []
     * - Boolean returns false
     * - String returns ''
     * - Escaped array returns []
     */
    public function testWithNullRegistryService(): void
    {
        // Test behavior when RegistryService returns null
        $testClass = new RequestGetterTestClass();

        // Don't set the RegistryService, so getServiceFromContainer returns null

        // Test all methods with null RegistryService
        $this->assertSame(0, $testClass->getIntegerRequestData('testKey'));
        $this->assertSame([], $testClass->getArrayRequestData('testKey'));
        $this->assertFalse($testClass->getBoolRequestData('testKey'));
        $this->assertSame('', $testClass->getStringRequestEscapedData('testKey'));
        $this->assertSame([], $testClass->getArrayRequestEscapedData('testKey'));
    }
}
