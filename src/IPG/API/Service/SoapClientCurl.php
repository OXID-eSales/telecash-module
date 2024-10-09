<?php

namespace OxidSolutionCatalysts\TeleCash\IPG\API\Service;

/**
 * Class SoapClientCurl
 *
 * Inspired by:
 * https://gist.github.com/stefanvangastel/698d5f08c7901f62744d
 */
class SoapClientCurl
{
    /** @var null|string User name for HTTP Basic authentication */
    private string|null $username = null;
    /** @var null|string Password for HTTP Basic authentication */
    private string|null $password = null;

    /** @var array<int|string, mixed> Options for CURL. */
    private array $curlOptions;

    /** @var mixed $curlStatusCode */
    private mixed $curlStatusCode;
    /** @var int $curlErrorNumber */
    private int $curlErrorNumber;
    /** @var string $curlErrorMsg */
    private string $curlErrorMsg;

    /**
     * @param array<int|string, mixed>  $curlOptions
     * @param string|null $username
     * @param string|null $password
     */
    public function __construct(array $curlOptions, string|null $username = null, string|null $password = null)
    {
        $this->username = $username;
        $this->password = $password;

        $defaults = [
            'url'          => null,
            'sslCert'      => null,
            'sslKey'       => null,
            'sslKeyPasswd' => null,
            'caInfo'       => null
        ];

        $this->curlOptions = array_merge($defaults, $curlOptions);
        $this->curlErrorMsg = '';
        $this->curlErrorNumber = 0;
        $this->curlStatusCode = 0;
    }

    /**
     * @param string $request
     *
     * @return mixed
     */
    protected function doRequest($request)
    {
        //Basic curl setup for SOAP call
        $curlHandle = $this->curlInit();
        $this->curlSetopt($curlHandle, CURLOPT_URL, $this->curlOptions['url']);
        $this->curlSetopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        $this->curlSetopt($curlHandle, CURLINFO_HEADER_OUT, 1);
        $this->curlSetopt($curlHandle, CURLOPT_HTTPHEADER, ['Content-Type: text/xml']);
        $this->curlSetopt($curlHandle, CURLOPT_POSTFIELDS, $request);
        $this->curlSetopt($curlHandle, CURLOPT_TIMEOUT, 30);
        $this->curlSetopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 5);

        //SSL
        $this->curlSetopt($curlHandle, CURLOPT_SSLCERT, $this->curlOptions['sslCert']);
        $pathinfo = pathinfo($this->curlOptions['sslCert']);
        if (isset($pathinfo['extension']) && strtolower($pathinfo['extension']) == 'p12') {
            $this->curlSetopt($curlHandle, CURLOPT_SSLCERTTYPE, "P12");
            $this->curlSetopt($curlHandle, CURLOPT_SSLCERTPASSWD, $this->curlOptions['sslKeyPasswd']);
        } else {
            $this->curlSetopt($curlHandle, CURLOPT_SSLKEY, $this->curlOptions['sslKey']);
            $this->curlSetopt($curlHandle, CURLOPT_SSLKEYPASSWD, $this->curlOptions['sslKeyPasswd']);
        }

        $this->curlSetopt($curlHandle, CURLOPT_SSL_VERIFYPEER, true);
        $this->curlSetopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
        $this->curlSetopt($curlHandle, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        $this->curlSetopt($curlHandle, CURLOPT_USERPWD, sprintf('%1$s:%2$s', $this->username, $this->password));

        $response               = $this->curlExec($curlHandle);
        $this->curlErrorNumber  = $this->curlErrno($curlHandle);

        if ($this->curlErrorNumber == CURLE_OK) {
            $this->curlStatusCode = $this->curlGetinfo($curlHandle, CURLINFO_HTTP_CODE);
        }
        $this->curlErrorMsg  = $this->curlError($curlHandle);

        //Close connection
        $this->curlClose($curlHandle);

        //Return response info
        return $response;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->curlStatusCode . ': ' . $this->curlErrorMsg;
    }

    protected function curlInit(): \CurlHandle|false
    {
        return curl_init();
    }

    protected function curlSetopt(mixed $handle, int $option, mixed $value): bool
    {
        return curl_setopt($handle, $option, $value);
    }

    protected function curlExec(mixed $handle): bool|string
    {
        return curl_exec($handle);
    }

    protected function curlErrno(mixed $handle): int
    {
        return curl_errno($handle);
    }

    protected function curlGetinfo(mixed $handle, mixed $opt = null): mixed
    {
        return curl_getinfo($handle, $opt);
    }

    protected function curlError(mixed $handle): string
    {
        return curl_error($handle);
    }

    protected function curlClose(mixed $handle): void
    {
        curl_close($handle);
    }
}
