<?php
namespace Iekadou\Quickies;

class PasswordField extends VarcharField {
    const _cn = "Iekadou\\Quickies\\PasswordField";

    public function get_sql_part($field_name, $field) {
        $max_length = (isset($field['max_length'])) ? $field['max_length'] : 254;
        return "`".$field_name."` varchar(".$max_length.") NOT NULL";
    }
}