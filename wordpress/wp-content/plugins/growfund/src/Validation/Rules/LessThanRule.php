<?php

namespace Growfund\Validation\Rules;

defined( 'ABSPATH' ) || exit;

use Growfund\Supports\Date;
use Growfund\Supports\Str;

/**
 * Rule to ensure the value is after the given date.
 *
 * @since 1.0.0
 */
class LessThanRule extends BaseRule
{
    /**
     * Check if the rule is valid.
     *
     * @return bool
     */
    public function validate_rule()
    {
        $current_value = $this->value;
        $target_value = $this->rule_value;

        if (!is_numeric($target_value) && array_key_exists($target_value, $this->data)) {
            $target_value = $this->data[$target_value];
        }

        $target_value = Str::to_number($target_value);

        if (is_int($target_value)) {
            $current_value = (int) $current_value;
        }

        if (is_float($target_value)) {
            $current_value = (float) $current_value;
        }

        return $current_value < $target_value;
    }

    /**
     * Get the error message if the rule is not valid.
     *
     * @return string
     */
    public function get_error_message()
    {
        /* translators: 1: field name, 2: rule value */
        return sprintf(__('The %1$s field must be greater than %2$s.', 'growfund'), $this->key, $this->rule_value);
    }
}
