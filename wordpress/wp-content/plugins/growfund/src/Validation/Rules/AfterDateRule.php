<?php

namespace Growfund\Validation\Rules;

defined( 'ABSPATH' ) || exit;

use Growfund\Supports\Date;

/**
 * Rule to ensure the value is after the given date.
 *
 * @since 1.0.0
 */
class AfterDateRule extends BaseRule
{
    /**
     * Check if the rule is valid.
     *
     * @return bool
     */
    public function validate_rule()
    {
        if (array_key_exists($this->rule_value, $this->data)) {
            return Date::is_after($this->value, $this->data[$this->rule_value], false);
        }

        return true;
    }

    /**
     * Get the error message if the rule is not valid.
     *
     * @return string
     */
    public function get_error_message()
    {
        /* translators: 1: field name, 2: rule value */
        return sprintf(__('The %1$s field must be after %2$s.', 'growfund'), str_replace(['_', '.'], ' ', $this->key), $this->rule_value);
    }
}
