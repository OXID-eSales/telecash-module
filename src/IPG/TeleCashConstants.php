<?php

namespace OxidSolutionCatalysts\TeleCash\IPG;

final class TeleCashConstants
{
    public const V1 = 'v1';
    public const A1 = 'a1';
    public const IPGAPI = 'ipgapi';

    public const PREF_V1 = self::V1 . ':';
    public const PREF_A1 = self::A1 . ':';
    public const PREF_IPGAPI = self::IPGAPI . ':';

    public const SCHEMA_URL = 'http://ipg-online.com/ipgapi/schemas/';
    public const NAMESPACE_V1 = self::SCHEMA_URL . self::V1;
    public const NAMESPACE_A1 = self::SCHEMA_URL . self::A1;
    public const NAMESPACE_IPGAPI = self::SCHEMA_URL . self::IPGAPI;

    public const NAMESPACE_SOAP = 'http://schemas.xmlsoap.org/soap/envelope/';

    public const SOAP_ERROR_SERVER = 'SOAP-ENV:Server';
    public const SOAP_ERROR_CLIENT = 'SOAP-ENV:Client';

    public const SOAP_CLIENT_ERROR_MERCHANT   = 'MerchantException';
    public const SOAP_CLIENT_ERROR_PROCESSING = 'ProcessingException';
}
