<?php

namespace Growfund\Validation\Rules;

defined( 'ABSPATH' ) || exit;

/**
 * Validates that a value is same as another specified field.
 *
 * @since 1.0.0
 */
class SameAsRule extends BaseRule
{
    /**
     * Determine if the value is matched.
     *
     * @return bool
     */
    public function validate_rule()
    {
        $required_value = $this->data[$this->rule_value] ?? null;

        return !is_null($this->value) && $this->value === $required_value;
    }

    /**
     * Get the error message for a missing field.
     *
     * @return string
     */
    public function get_error_message()
    {
        /* translators: 1: field name, 2: rule value */
        return sprintf(__('The %1$s field must be same as %2$s.', 'growfund'), $this->key, $this->rule_value);
    }
}
