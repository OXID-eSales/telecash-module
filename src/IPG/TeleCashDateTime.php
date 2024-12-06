<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\TeleCash\IPG;

use DateInvalidTimeZoneException;
use DateMalformedStringException;
use DateTime;
use DateTimeZone;

/**
 * Class for handling TeleCash DateTime
 */
class TeleCashDateTime
{
    /**
     * Formats a DateTime object to a string, using the telecash-specific format.
     * Make sure that the timezone is set according to the form-value.
     *
     * @param string $dateTime - e.g. '2024-10-14 18:06:39'
     * @param string $timeZone - e.g. 'Europe/Berlin'
     *
     * @return string
     *
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     */
    public function formatDateTime(string $dateTime = '', string $timeZone = 'Europe/Berlin'): string
    {
        $dateTimeObj = $this->getDateTime($dateTime, $timeZone);
        $format = 'Y:m:d-H:i:s';
        return $dateTimeObj->format($format);
    }

    /**
     * Builds a DateTime object from a $dateTime string and a optional $timeZone string
     *
     * @param string $dateTime - e.g. '2024-10-14 18:06:39'
     * @param string $timeZone - e.g. 'Europe/Berlin'
     *
     * @return DateTime
     *
     * @throws DateMalformedStringException
     * @throws DateInvalidTimeZoneException
     */
    public function getDateTime(string $dateTime = '', string $timeZone = 'Europe/Berlin'): DateTime
    {
        return new DateTime($dateTime, new DateTimeZone($timeZone));
    }

    /**
     * Formats a DateTime object to a string, using the telecash-specific format.
     * Make sure that the timezone is set according to the form-value.
     *
     * @param string $dateTime - e.g. '2024-10-14 18:06:39'
     * @param string $timeZone - e.g. 'Europe/Berlin'
     *
     * @return int
     *
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     */
    public function getTimeStamp(string $dateTime = '', string $timeZone = 'Europe/Berlin'): int
    {
        return $this->getDateTime($dateTime, $timeZone)->getTimestamp();
    }
}
