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
     * Formats a DateTime string to a string, using the telecash-specific format.
     * Make sure that the timezone is set according to the form-value.
     *
     * @param string $dateTime - e.g. '2024-10-14 18:06:39'
     * @param string $timeZone - e.g. 'Europe/Berlin'
     *
     * @return string
     */
    public function formatDateTime(string $dateTime = '', string $timeZone = 'Europe/Berlin'): string
    {
        $dateTimeObj = $this->getDateTime($dateTime, $timeZone);
        return $this->buildTeleCashFormatDateTime($dateTimeObj);
    }

    /**
     * Builds a DateTime object from a $dateTime string and a optional $timeZone string
     *
     * @param string $dateTime - e.g. '2024-10-14 18:06:39'
     * @param string|null $timeZone - e.g. 'Europe/Berlin'
     *
     * @return DateTime
     */
    public function getDateTime(string $dateTime = '', ?string $timeZone = null): DateTime
    {
        try {
            $timeZoneObj = $timeZone ? new DateTimeZone($timeZone) : null;
        } catch (DateInvalidTimeZoneException) {
            $timeZoneObj = null;
        }

        try {
            $dateTimeObj = new DateTime($dateTime, $timeZoneObj);
        } catch (DateMalformedStringException) {
            $dateTimeObj = new DateTime();
        }

        return $dateTimeObj;
    }

    /**
     * Formats a timestamp, using the telecash-specific format.
     * Make sure that the timezone is set according to the form-value.
     *
     * @param int $timeStamp
     *
     * @return string
     */
    public function getFormatDateTimeFromTimeStamp(int $timeStamp): string
    {
        $dateTimeObj = new DateTime();
        $dateTimeObj->setTimestamp($timeStamp);
        return $this->buildTeleCashFormatDateTime($dateTimeObj);
    }

    /**
     * Formats a DateTime object to a string, using the telecash-specific format.
     * Make sure that the timezone is set according to the form-value.
     *
     * @param string $dateTime - e.g. '2024-10-14 18:06:39'
     * @param string $timeZone - e.g. 'Europe/Berlin'
     *
     * @return int
     */
    public function getTimeStamp(string $dateTime = '', string $timeZone = 'Europe/Berlin'): int
    {
        return $this->getDateTime($dateTime, $timeZone)->getTimestamp();
    }

    /**
     * Formats a DateTime object to a string, using the telecash-specific format.
     * Make sure that the timezone is set according to the form-value.
     *
     * @param DateTime $dateTimeObj
     *
     * @return string
     */
    private function buildTeleCashFormatDateTime(DateTime $dateTimeObj): string
    {
        $format = 'Y:m:d-H:i:s';
        return $dateTimeObj->format($format);
    }
}
