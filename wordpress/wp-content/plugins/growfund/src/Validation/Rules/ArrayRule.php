<?php

namespace Growfund\Validation\Rules;

defined( 'ABSPATH' ) || exit;

/**
 * Validates that a value is an array.
 *
 * @since 1.0.0
 */
class ArrayRule extends BaseRule
{
    /**
     * check for strict data type 
     * 
     * @var bool
     */
    protected $check_strict_data_type = true;

    /**
     * Check if the value is a valid array.
     *
     * @return bool
     */
    public function validate_rule()
    {
        return is_array($this->value);
    }

    /**
     * Get the error message for an invalid array value.
     *
     * @return string
     */
    public function get_error_message()
    {
        /* translators: %s: field name */
        return sprintf(__('The %s field must be of type array.', 'growfund'), $this->key);
    }
}
