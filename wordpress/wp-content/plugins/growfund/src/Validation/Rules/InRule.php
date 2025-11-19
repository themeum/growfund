<?php

namespace Growfund\Validation\Rules;

defined( 'ABSPATH' ) || exit;

/**
 * Rule to ensure a value exists within a predefined set.
 *
 * @since 1.0.0
 */
class InRule extends BaseRule
{
    /**
     * Check if the value is in the allowed list.
     *
     * @return bool
     */
    public function validate_rule()
    {
        $in = $this->rule_value;

        if (is_string($in)) {
            $in = str_replace(' ', '', $in);
            $in = explode(',', $in);
        }

        return in_array($this->value, $in); // phpcs:ignore -- intentionally ignored
    }

    /**
     * Get the error message if the value is not in the allowed list.
     *
     * @return string
     */
    public function get_error_message()
    {
        /* translators: 1: field name, 2: rule value */
        return sprintf(__('The %1$s field must contain a value from: %2$s.', 'growfund'), $this->key, $this->rule_value);
    }
}
