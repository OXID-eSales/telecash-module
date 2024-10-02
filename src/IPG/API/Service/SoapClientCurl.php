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
     * @param string $username
     * @param string $password
     */
    public function __construct(array $curlOptions, $username = null, $password = null)
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
    }

    /**
     * @param string $request
     *
     * @return mixed
     */
    protected function doRequest($request)
    {
        //Basic curl setup for SOAP call
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $this->curlOptions['url']);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLINFO_HEADER_OUT, 1);
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, ['Content-Type: text/xml']);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $request);
        curl_setopt($curlHandle, CURLOPT_TIMEOUT, 30);
        curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 5);

        //SSL
        curl_setopt($curlHandle, CURLOPT_CAINFO, $this->curlOptions['caInfo']);
        curl_setopt($curlHandle, CURLOPT_SSLVERSION, 'CURL_SSLVERSION_TLSv1');
        curl_setopt($curlHandle, CURLOPT_SSLCERT, $this->curlOptions['sslCert']);
        curl_setopt($curlHandle, CURLOPT_SSLKEY, $this->curlOptions['sslKey']);
        curl_setopt($curlHandle, CURLOPT_SSLKEYPASSWD, $this->curlOptions['sslKeyPasswd']);
        curl_setopt($curlHandle, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curlHandle, CURLOPT_USERPWD, sprintf('%1$s:%2$s', $this->username, $this->password));

        $response               = curl_exec($curlHandle);
        $this->curlErrorNumber  = curl_errno($curlHandle);

        if ($this->curlErrorNumber == CURLE_OK) {
            $this->curlStatusCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
        }
        $this->curlErrorMsg  = curl_error($curlHandle);

        //Close connection
        curl_close($curlHandle);

        //Return response info
        return $response;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->curlStatusCode . ': ' . $this->curlErrorMsg;
    }
}
