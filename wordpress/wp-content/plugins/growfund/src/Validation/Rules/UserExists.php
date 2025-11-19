<?php

namespace Growfund\Validation\Rules;

defined( 'ABSPATH' ) || exit;

/**
 * Rule to ensure a a user exist with id.
 *
 * @since 1.0.0
 */
class UserExists extends BaseRule
{
    /**
     * Check if the user exist
     *
     * @return bool
     */
    public function validate_rule()
    {
        if (empty($this->value)) {
            return true;
        }

        return get_userdata($this->value) !== false;
    }

    /**
     * Get the error message if the post does not exist.
     *
     * @return string
     */
    public function get_error_message()
    {
        /* translators: %s: field value */
        return sprintf(__('User with id %s does not exist.', 'growfund'), $this->value);
    }
}
