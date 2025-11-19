<?php

namespace Growfund\Validation\Rules;

defined( 'ABSPATH' ) || exit;

/**
 * Validates that the given value is a valid url.
 *
 * @since 1.0.0
 */
class UrlRule extends BaseRule
{
    /**
     * Determine if the value is a url.
     *
     * @return bool
     */
    public function validate_rule()
    {
        return filter_var($this->value, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Get the error message for invalid url.
     *
     * @return string
     */
    public function get_error_message()
    {
        /* translators: %s: field name */
        return sprintf(__('The %s field must be of type url.', 'growfund'), $this->key);
    }
}
