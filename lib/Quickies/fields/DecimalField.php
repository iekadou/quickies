<?php
namespace Iekadou\Quickies;

class DecimalField extends Field {
    const _cn = "Iekadou\\Quickies\\DecimalField";

    public function get_sql_part($field_name, $field) {
        $pre_dot_precision = (isset($field['pre_dot_precision'])) ? $field['pre_dot_precision'] : 10;
        $pos_dot_precision = (isset($field['pos_dot_precision'])) ? $field['post_dot_precision'] : 5;
        return "`".$field_name."` DECIMAL(".$pre_dot_precision.", ".$pos_dot_precision.") NOT NULL";
    }
}