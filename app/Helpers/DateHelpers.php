<?php

namespace App\Helpers;

use Exception;
use Illuminate\Support\Facades\App;
use IntlDateFormatter;

class DateHelpers
{
    public static function convertIsoPatternToDateTime($isoPattern)
    {
        $isoPattern = str_replace('yyyy', 'Y', $isoPattern);
        $isoPattern = str_replace('yy', 'y', $isoPattern);
        $isoPattern = str_replace('MMMM', 'F', $isoPattern);
        $isoPattern = str_replace('MMM', 'M', $isoPattern);
        $isoPattern = str_replace('MM', 'm', $isoPattern);
        $isoPattern = str_replace('dd', 'd', $isoPattern);
        $isoPattern = str_replace('HH:mm', 'H:i', $isoPattern);
        $isoPattern = str_replace('HH', 'H', $isoPattern);
        $isoPattern = str_replace('mm', 'i', $isoPattern);

        return $isoPattern;
    }

    /**
     * Note: Requires PHP intl extension
     *
     * Source: https://stackoverflow.com/a/41779850
     */
    public static function getDateFormat($locale = null)
    {
        if ($locale === null)
            $locale = App::currentLocale();

        $formatter = new IntlDateFormatter($locale, IntlDateFormatter::SHORT, IntlDateFormatter::SHORT);
        if ($formatter === null)
            throw new Exception(intl_get_error_message());

        $pattern = $formatter->getPattern();
        $pattern = self::convertIsoPatternToDateTime($pattern);

        return $pattern;
    }

    /**
     * Note: Requires PHP intl extension
     *
     * Source: https://stackoverflow.com/a/41779850
     */
    public static function formatDateTime($dateTime, $locale = null)
    {
        return $dateTime->format(self::getDateFormat($locale));
    }
}
