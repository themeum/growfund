<?php

namespace Growfund\Validation\Rules;

defined( 'ABSPATH' ) || exit;

/**
 * Validates that a value is present and not null.
 *
 * @since 1.0.0
 */
class ProhibitedRule extends BaseRule
{
    /**
     * Determine if the value is present.
     *
     * @return bool
     */
    public function validate_rule()
    {
        return empty($this->value);
    }

    /**
     * Get the error message for the prohibited field.
     *
     * @return string
     */
    public function get_error_message()
    {
        /* translators: %s: field name */
        return sprintf(__('The %s field is prohibited.', 'growfund'), str_replace(['_', '.'], ' ', $this->key));
    }

    protected function ignore_rule_check()
    {
        return false;
    }
}
