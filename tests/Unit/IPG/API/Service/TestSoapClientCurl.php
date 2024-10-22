<?php

namespace OxidSolutionCatalysts\TeleCash\Tests\Unit\IPG\API\Service;

use OxidSolutionCatalysts\TeleCash\IPG\API\Service\SoapClientCurl;

/**
 * Testbare Version der SoapClientCurl Klasse mit öffentlichen Curl-Methoden
 */
class TestSoapClientCurl extends SoapClientCurl
{
    public function publicCurlInit(): \CurlHandle|false
    {
        return parent::curlInit();
    }

    public function publicCurlSetopt(mixed $handle, int $option, mixed $value): bool
    {
        return parent::curlSetopt($handle, $option, $value);
    }

    public function publicCurlExec(mixed $handle): bool|string
    {
        return parent::curlExec($handle);
    }

    public function publicCurlErrno(mixed $handle): int
    {
        return parent::curlErrno($handle);
    }

    public function publicCurlGetinfo(mixed $handle, mixed $opt = null): mixed
    {
        return parent::curlGetinfo($handle, $opt);
    }

    public function publicCurlError(mixed $handle): string
    {
        return parent::curlError($handle);
    }

    public function publicCurlClose(mixed $handle): void
    {
        parent::curlClose($handle);
    }
}
