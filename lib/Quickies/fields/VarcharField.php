<?php
namespace Iekadou\Quickies;

class VarcharField extends Field {
    const _cn = "Iekadou\\Quickies\\VarcharField";

    public function _validate($obj, $field_name, $value) {
        if (isset($obj->fields[$field_name]['regex'])) {
            if (!preg_match($obj->fields[$field_name]['regex'], $value)) {
                $obj->errors[] = $field_name;
                return false;
            }
        }
        if ((!isset($value) || $value == '') && isset($obj->fields[$field_name]['required']) && $obj->fields[$field_name]['required']) {
            return false;
        } else {
            if (isset($obj->fields[$field_name]['max_length'])) {
                if (strlen($value) > $obj->fields[$field_name]['max_length']) {
                    return false;
                }
            }
            if (isset($obj->fields[$field_name]['min_length'])) {
                if (strlen($value) < $obj->fields[$field_name]['min_length']) {
                    return false;
                }
            }
        }
        return true;
    }

    public function get_sql_part($field_name, $field) {
        $max_length = (isset($field['max_length'])) ? $field['max_length'] : 254;
        return "`".$field_name."` varchar(".$max_length.") NOT NULL";
    }
}
