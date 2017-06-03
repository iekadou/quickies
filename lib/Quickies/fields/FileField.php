<?php
namespace Iekadou\Quickies;

class FileField extends Field {
    const _cn = "Iekadou\\Quickies\\FileField";

    public function _validate($obj, $field_name, $value) {
        if ((!isset($value) || $value == '') && isset($obj->fields[$field_name]['required']) && $obj->fields[$field_name]['required']) {
            return false;
        }
        return true;
    }

    public function get_sql_part($field_name, $field) {
        $max_length = (isset($field['max_length'])) ? $field['max_length'] : 254;
        return "`".$field_name."` varchar(".$max_length.") NOT NULL";
    }
}
