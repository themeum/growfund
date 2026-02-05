<?php

namespace Growfund\Supports;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\DateTimeFormats;
use DateTime;

class Date
{
    /**
     * Make the date compatible with zod date type
     *
     * @param string $date
     * @return string
     */
    public static function zod_safe(string $date)
    {
        return (new DateTime($date))->format(DateTime::ATOM);
    }

    /**
     * Make the date/datetime compatible with sql date type
     *
     * @param string $date
     * @param bool $without_time
     * @return string
     */
    public static function sql_safe(string $date, bool $without_time = false)
    {
        $gmt = gmdate(DateTimeFormats::DB_DATETIME, strtotime($date));

        if ($without_time) {
            return (new DateTime($gmt))->format(DateTimeFormats::DB_DATE);
        }

        return (new DateTime($gmt))->format(DateTimeFormats::DB_DATETIME);
    }

    /**
     * Make the current date/datetime compatible with sql date type
     *
     * @param bool $without_time
     * @return string
     */
    public static function current_sql_safe(bool $without_time = false)
    {
        $gmt = gmdate(DateTimeFormats::DB_DATETIME);

        if ($without_time) {
            return (new DateTime($gmt))->format(DateTimeFormats::DB_DATE);
        }

        return (new DateTime($gmt))->format(DateTimeFormats::DB_DATETIME);
    }

    /**
     * Check if the date is in the future
     *
     * @param string $date
     * @return bool
     */
    public static function is_date_in_future(string $date)
    {
        return (new DateTime($date))->getTimestamp() > time();
    }

    /**
     * Check if the date is in the past
     *
     * @param string $date
     * @return bool
     */
    public static function is_date_in_past(string $date)
    {
        return (new DateTime($date))->getTimestamp() < time();
    }

    /**
     * Check if the date is in the future or present
     *
     * @param string $date
     * @return bool
     */
    public static function is_date_in_future_or_present(string $date)
    {
        return (new DateTime($date))->getTimestamp() >= time();
    }

    /**
     * Check if the date is in the past or present
     *
     * @param string $date
     * @return bool
     */
    public static function is_date_in_past_or_present(string $date)
    {
        return (new DateTime($date))->getTimestamp() <= time();
    }

    /**
     * Check if the two dates are the same
     *
     * @param string $date1
     * @param string $date2
     * @return bool
     */
    public static function is_same(string $date1, string $date2)
    {
        return (new DateTime($date1))->format(DateTimeFormats::DB_DATE) === (new DateTime($date2))->format(DateTimeFormats::DB_DATE);
    }

    /**
     * Check if the date is after the given date
     *
     * @param string $date1
     * @param string $date2
     * @return boolean
     */
    public static function is_after(string $date1, string $date2, bool $strict = true)
    {
        if (!$strict) {
            return (new DateTime(static::format($date1, DateTimeFormats::DB_DATE)))->getTimestamp() > (new DateTime(static::format($date2, DateTimeFormats::DB_DATE)))->getTimestamp();
        }

        return (new DateTime($date1))->getTimestamp() > (new DateTime($date2))->getTimestamp();
    }

    /**
     * Check if the date is before the given date
     *
     * @param string $date1
     * @param string $date2
     * @return boolean
     */
    public static function is_before(string $date1, string $date2)
    {
        return (new DateTime($date1))->getTimestamp() < (new DateTime($date2))->getTimestamp();
    }

    /**
     * Get the start and end date of the last 30 days
     *
     * @return array
     */
    public static function start_and_end_date_of_last_thirty_days()
    {
        $gmt = gmdate(DateTimeFormats::DB_DATETIME);
        $date = new DateTime($gmt);
        $start_date = $date->modify('-30 days')->format(DateTimeFormats::DB_DATE);
        $end_date = (new DateTime())->format(DateTimeFormats::DB_DATE);

        return [$start_date, $end_date];
    }

    /**
     * Get the human readable time difference between the current time and the given date
     *
     * @param string $date
     * @return string
     */
    public static function human_readable_time_diff(string $date)
    {
        $suffix = 'ago';

        if (static::is_date_in_future_or_present($date)) {
            $suffix = ' left';
        }

        return human_time_diff(strtotime($date), time()) . ' ' . $suffix; 
    }

    /**
     * Format the date
     *
     * @param string|int $date
     * @param string $format
     * @return string
     */
    public static function format($date, string $format = DateTimeFormats::DB_DATETIME)
    {
        if (empty($date)) {
            return '';
        }

        if (is_int($date)) {
            return (new DateTime('@' . $date))->format($format);
        }

        return (new DateTime($date))->format($format);
    }
}
