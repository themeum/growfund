<?php

namespace Growfund\Validation\Rules;

defined( 'ABSPATH' ) || exit;

/**
 * Validates that the given value with the given regex
 *
 * @since 1.0.0
 */
class RegexRule extends BaseRule
{
    /**
     * Determine if the value is valid based on the regex.
     *
     * @return bool
     */
    public function validate_rule()
    {
        return preg_match($this->rule_value, $this->value);
    }

    /**
     * Get the error message for invalid value.
     *
     * @return string
     */
    public function get_error_message()
    {
        /* translators: 1: field name, 2: rule value */
        return sprintf(__('The %1$s field must match the regex: %2$s.', 'growfund'), str_replace(['_', '.'], ' ', $this->key), $this->rule_value);
    }
}
