<?php

namespace Growfund\Validation\Rules;

defined( 'ABSPATH' ) || exit;

/**
 * Validates that a value is present and not null.
 *
 * @since 1.0.0
 */
class RequiredIfExists extends BaseRule
{
    /**
     * Determine if the value is present.
     *
     * @return bool
     */
    public function validate_rule()
    {
        if (array_key_exists($this->rule_value, $this->data) && !empty($this->data[$this->rule_value])) {
            if ($this->is_rule_applied('file')) {
                return !empty($this->value);
            }

            return !is_null($this->value) && $this->value !== '';
        }

        return true;
    }

    /**
     * Get the error message for a missing required field.
     *
     * @return string
     */
    public function get_error_message()
    {
        /* translators: %s: field name */
        return sprintf(__('The %s field is required.', 'growfund'), str_replace(['_', '.'], ' ', $this->key));
    }

    protected function ignore_rule_check()
    {
        return false;
    }
}
