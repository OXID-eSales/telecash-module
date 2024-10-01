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
    private $username = null;
    /** @var null|string Password for HTTP Basic authentication */
    private $password = null;

    /** @var array Options for CURL. */
    private $curlOptions;

    /** @var mixed $curlStatusCode */
    private $curlStatusCode;
    /** @var int $curlErrorNumber */
    private $curlErrorNumber;
    /** @var string $curlErrorMsg */
    private $curlErrorMsg;

    /**
     * @param array  $curlOptions
     * @param string $username
     * @param string $password
     */
    public function __construct($curlOptions, $username = null, $password = null)
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
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->curlOptions['url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: text/xml']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

        //SSL
        curl_setopt($ch, CURLOPT_CAINFO, $this->curlOptions['caInfo']);
        curl_setopt($ch, CURLOPT_SSLVERSION, 'CURL_SSLVERSION_TLSv1');
        curl_setopt($ch, CURLOPT_SSLCERT, $this->curlOptions['sslCert']);
        curl_setopt($ch, CURLOPT_SSLKEY, $this->curlOptions['sslKey']);
        curl_setopt($ch, CURLOPT_SSLKEYPASSWD, $this->curlOptions['sslKeyPasswd']);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, sprintf('%1$s:%2$s', $this->username, $this->password));

        $response               = curl_exec($ch);
        $this->curlErrorNumber  = curl_errno($ch);

        if ($this->curlErrorNumber == CURLE_OK) {
            $this->curlStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        }
        $this->curlErrorMsg  = curl_error($ch);

        //Close connection
        curl_close($ch);

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
