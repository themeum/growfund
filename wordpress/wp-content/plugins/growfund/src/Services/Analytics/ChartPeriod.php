<?php

namespace Growfund\Services\Analytics;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\DateTimeFormats;
use DateInterval;
use DatePeriod;
use DateTime;

class ChartPeriod
{
    const DAILY = 'daily';
    const THREE_DAYS = 'three_days';
    const WEEKLY = 'weekly';
    const THREE_WEEKS = 'three_weeks';
    const MONTHLY = 'monthly';
    const THREE_MONTHS = 'three_months';

    const FifteenDays = 15;
    const ThirtyDays = 30;
    const NinetyDays = 90;
    const HundredEightyDays = 180;
    const FiveHundredSixtyDays = 500;

    public $start_date;
    public $end_date;

    public $chart_type;


    public function __construct(DateTime $start_date, DateTime $end_date)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->set_type();
    }

    public function set_type()
    {
        $date_diff = $this->start_date->diff($this->end_date)->days;

        if ($date_diff > self::FifteenDays && $date_diff <= self::ThirtyDays) {
            $this->chart_type = self::THREE_DAYS;
            return;
        }

        if ($date_diff > self::ThirtyDays && $date_diff <= self::NinetyDays) {
            $this->chart_type = self::WEEKLY;
            return;
        }

        if ($date_diff > self::NinetyDays && $date_diff <= self::HundredEightyDays) {
            $this->chart_type = self::THREE_WEEKS;
            return;
        }

        if ($date_diff > self::HundredEightyDays && $date_diff <= self::FiveHundredSixtyDays) {
            $this->chart_type = self::MONTHLY;
            return;
        }

        if ($date_diff > self::FiveHundredSixtyDays) {
            $this->chart_type = self::THREE_MONTHS;
            return;
        }

        $this->chart_type = self::DAILY;
    }

    public function get_interval()
    {
        switch ($this->chart_type) {
            case self::THREE_MONTHS:
                return DateTimeFormats::THREE_MONTH_STEP;
            case self::MONTHLY:
                return DateTimeFormats::PER_MONTH_STEP;
            case self::THREE_WEEKS:
                return DateTimeFormats::THREE_WEEK_STEP;
            case self::WEEKLY:
                return DateTimeFormats::PER_WEEK_STEP;
            case self::THREE_DAYS:
                return DateTimeFormats::THREE_DAY_STEP;
            case self::DAILY:
            default:
                return DateTimeFormats::PER_DAY_STEP;
        }
    }

    public function get_label_format()
    {
        switch ($this->chart_type) {
            case self::THREE_MONTHS:
            case self::MONTHLY:
                return DateTimeFormats::HUMAN_READABLE_MONTH_OF_YEAR;
            case self::THREE_WEEKS:
            case self::WEEKLY:
                return DateTimeFormats::HUMAN_READABLE_DAY_OF_MONTH;
            case self::THREE_DAYS:
            case self::DAILY:
            default:
                return DateTimeFormats::HUMAN_READABLE_DAY_OF_MONTH;
        }
    }

    public function get_date_format()
    {
        switch ($this->chart_type) {
            case self::THREE_MONTHS:
            case self::MONTHLY:
                return DateTimeFormats::DB_MONTH_OF_YEAR;
            case self::THREE_WEEKS:
            case self::WEEKLY:
                return DateTimeFormats::DB_DATE;
            case self::THREE_DAYS:
            case self::DAILY:
            default:
                return DateTimeFormats::DB_DATE;
        }
    }

    public function get_date_period()
    {
        return new DatePeriod($this->start_date, new DateInterval($this->get_interval()), $this->end_date->modify('+1 day'));
    }
}
