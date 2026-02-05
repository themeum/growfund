<?php

namespace Growfund\Validation\Rules;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Validation;

/**
 * Rule to ensure the minium value.
 *
 * @since 1.0.0
 */
class MinRule extends BaseRule
{
    /**
     * Check if the value is valid.
     *
     * @return bool
     */
    public function validate_rule()
    {
        if ($this->is_string_value()) {
            return strlen($this->value) >= $this->rule_value;
        } elseif ($this->is_array_value()) {
            return count($this->value) >= $this->rule_value;
        }

        return $this->value >= $this->rule_value;
    }

    /**
     * Get the error message if the value is less than the min value.
     *
     * @return string
     */
    public function get_error_message()
    {
        if ($this->is_string_value()) {
            /* translators: 1: field name, 2: rule value */
            return sprintf(__('The %1$s field must be greater than or equal to %2$s characters.', 'growfund'), str_replace(['_', '.'], ' ', $this->key), $this->rule_value);
        } elseif ($this->is_array_value()) {
            /* translators: 1: field name, 2: rule value */
            return sprintf(__('The %1$s field must be greater than or equal to %2$s items.', 'growfund'), str_replace(['_', '.'], ' ', $this->key), $this->rule_value);
        }

        /* translators: 1: field name, 2: rule value */
        return sprintf(__('The %1$s field must be greater than or equal %2$s.', 'growfund'), str_replace(['_', '.'], ' ', $this->key), $this->rule_value);
    }

    protected function is_string_value()
    {
        $is_string = in_array('string', $this->all_applied_rules, true) || in_array(Validation::RULE_MAP['string'], $this->all_applied_rules, true);

        return $is_string || (is_string($this->value) && !is_numeric($this->value));
    }

    protected function is_array_value()
    {
        $is_array = in_array('array', $this->all_applied_rules, true) || in_array(Validation::RULE_MAP['array'], $this->all_applied_rules, true);

        return $is_array || is_array($this->value);
    }
}
