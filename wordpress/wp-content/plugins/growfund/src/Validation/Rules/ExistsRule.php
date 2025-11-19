<?php

namespace Growfund\Validation\Rules;

defined( 'ABSPATH' ) || exit;

use Growfund\QueryBuilder;
use Exception;

/**
 * Rule to ensure a a post exist with id and post type.
 *
 * @since 1.0.0
 */
class ExistsRule extends BaseRule
{
    /**
     * Check if the post exist and meet other rules like post_type, status, parent
     *
     * @return bool
     */
    public function validate_rule()
    {
        if (stripos($this->rule_value, ',') === false) {
            throw new Exception(esc_html__("Missing parameters for exists rule.", 'growfund'));
        }

        list($table_name, $column_name) = explode(",", $this->rule_value, 2);

        $result = QueryBuilder::query()->table($table_name)->where($column_name, $this->value)->first();

        return !empty($result);
    }

    /**
     * Get the error message if the row does not exist in DB table.
     *
     * @return string
     */
    public function get_error_message()
    {
        return __('Item does not exist.', 'growfund');
    }
}
