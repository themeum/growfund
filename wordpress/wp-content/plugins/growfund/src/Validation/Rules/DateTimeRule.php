<?php

namespace Growfund\Validation\Rules;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\DateTimeFormats;
use DateTime;

/**
 * Validates that the given value matches db date time format.
 *
 * @since 1.0.0
 */
class DateTimeRule extends BaseRule
{
    /**
     * Determine if the value is a valid date in the given format.
     *
     * @return bool
     */
    public function validate_rule()
    {
        $date = DateTime::createFromFormat(DateTimeFormats::DB_DATETIME, $this->value);
        $iso_date = DateTime::createFromFormat(DateTime::ATOM, $this->value);
        
        return ($date && $date->format(DateTimeFormats::DB_DATETIME) === $this->value) 
            || ($iso_date && $iso_date->format(DateTime::ATOM) === $this->value);
    }

    /**
     * Get the error message for invalid date format.
     *
     * @return string
     */
    public function get_error_message()
    {
        /* translators: 1: field name, 2: db datetime format */
        return sprintf(__('The %1$s field must be a valid date time in the format %2$s.', 'growfund'), $this->key, DateTimeFormats::DB_DATETIME);
    }
}
