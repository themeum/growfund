<?php 

namespace Growfund\Supports;

defined('ABSPATH') || exit;

class MetaQueryFilter
{
    public function update_meta_value_is_null($where) {
        $placeholder = ".meta_value = 'IS NULL'";
        $replacement = ".meta_value IS NULL";

        if (strpos($where, $placeholder) !== false) {
            return str_replace($placeholder, $replacement, $where);
        }

        return $where;
    }

    public function update_meta_value_is_not_null($where) {
        $placeholder = ".meta_value = 'IS NOT NULL'";
        $replacement = ".meta_value IS NOT NULL";
        
        if (strpos($where, $placeholder) !== false) {
            return str_replace($placeholder, $replacement, $where);
        }

        return $where;
    }
}
