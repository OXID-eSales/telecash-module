<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\Traits\Traits;

use OxidEsales\Eshop\Core\Utils;
use OxidSolutionCatalysts\TeleCash\Core\Service\RegistryService;
use OxidSolutionCatalysts\TeleCash\Tests\Unit\Traits\TestClasses\JsonTestClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Integration Test for JSON Trait
 *
 * Tests the functionality of the JSON trait which provides methods for:
 * - Reading JSON POST data
 * - Converting between JSON strings and arrays
 * - Sending JSON responses
 *
 * The test suite verifies:
 * - Correct JSON encoding/decoding
 * - Proper error handling
 * - Appropriate HTTP header setting
 * - Response output functionality
 */
class JsonTest extends TestCase
{
    private JsonTestClass $testClass;
    private RegistryService&MockObject $registryService;
    private Utils&MockObject $utils;

    /**
     * Sets up the test environment before each test
     *
     * Creates:
     * - Mock for RegistryService
     * - Mock for Utils
     * - Instance of test class with JSON trait
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create mocks
        $this->utils = $this->createMock(Utils::class);
        $this->registryService = $this->createMock(RegistryService::class);
        $this->registryService->method('getUtils')
            ->willReturn($this->utils);

        // Initialize test class
        $this->testClass = new JsonTestClass();
        $this->testClass->setRegistryService($this->registryService);
    }

    /**
     * Tests JSON string to array conversion
     *
     * Verifies:
     * - Valid JSON is correctly converted to array
     * - Invalid JSON returns empty array
     * - Non-array JSON returns empty array
     * - Empty input returns empty array
     */
    public function testJsonToArray(): void
    {
        $testCases = [
            // Valid JSON cases
            [
                'input' => '{"key": "value", "number": 42}',
                'expected' => ['key' => 'value', 'number' => 42]
            ],
            [
                'input' => '[1, 2, 3]',
                'expected' => [1, 2, 3]
            ],
            // Invalid JSON cases
            [
                'input' => '{"invalid": json}',
                'expected' => []
            ],
            [
                'input' => '"not an array"',
                'expected' => []
            ],
            [
                'input' => '',
                'expected' => []
            ]
        ];

        foreach ($testCases as $case) {
            $result = $this->testClass->jsonToArrayTest($case['input']);
            $this->assertSame($case['expected'], $result);
            $this->assertIsArray($result);
        }
    }

    /**
     * Tests array to JSON string conversion
     *
     * Verifies:
     * - Arrays are correctly converted to JSON
     * - Numeric strings are converted to numbers
     * - Pretty printing is enabled
     * - Empty array produces valid JSON
     */
    public function testArrayToJson(): void
    {
        $testCases = [
            // Simple array - note that numbers stay as strings now
            [
                'input' => ['key' => 'value', 'number' => '42'],
                'expectedContains' => ['"key": "value"', '"number": "42"']  // Changed: number remains string
            ],
            // Nested array
            [
                'input' => ['nested' => ['deep' => 'value']],
                'expectedContains' => ['"nested": {', '"deep": "value"']
            ],
            // Array with multiple types - numeric strings remain strings
            [
                'input' => [
                    'string' => 'text',
                    'number' => '42',  // Changed: explicitly as string
                    'boolean' => true
                ],
                'expectedContains' => [
                    '"string": "text"',
                    '"number": "42"',  // Changed: expect string format
                    '"boolean": true'
                ]
            ],
            // Empty array
            [
                'input' => [],
                'expectedContains' => ['{}']
            ],
            // Test case for processor response code
            [
                'input' => ['processor_response_code' => '00'],
                'expectedContains' => ['"processor_response_code": "00"']  // Specifically test leading zeros
            ]
        ];

        foreach ($testCases as $case) {
            $result = $this->testClass->arrayToJsonTest($case['input']);

            // Verify JSON structure
            foreach ($case['expectedContains'] as $expected) {
                $this->assertStringContainsString(
                    $expected,
                    $result,
                    "Failed to find expected content in JSON result"
                );
            }

            // Verify the JSON is valid and maintains data types
            $this->assertIsString($result);
            $decodedResult = json_decode($result, true);
            $this->assertIsArray($decodedResult);
            $this->assertEquals(
                $case['input'],
                $decodedResult,
                "JSON conversion altered the original data"
            );
        }
    }

    /**
     * Tests JSON output functionality
     *
     * Verifies:
     * - Correct Content-Type header is set
     * - JSON content is output correctly
     * - Script execution is terminated
     */
    public function testOutputJson(): void
    {
        $testJson = '{"test": "value"}';

        // Expect header to be set
        $this->utils->expects($this->once())
            ->method('setHeader')
            ->with('Content-Type: application/json');

        // Expect content to be output and script terminated
        $this->utils->expects($this->once())
            ->method('showMessageAndExit')
            ->with($testJson);

        $this->testClass->outputJsonTest($testJson);
    }

    /**
     * Tests retrieving POST data
     *
     * Note: This test is limited as php://input cannot be easily mocked.
     * In a real environment, this would read from the PHP input stream.
     */
    public function testGetJsonPostData(): void
    {
        $result = $this->testClass->getJsonPostDataTest();
        $this->assertIsString($result);
    }
}
