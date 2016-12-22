<?php
namespace Iekadou\Quickies;

class TimestampField extends Field {
    const _cn = "Iekadou\\Quickies\\TimestampField";

    public function _set($obj, $field_name, $value) {
        if (is_numeric($value)) {
            $obj->set_data($field_name, date("Y-m-d H:i:s", $value));
        } else {
            $obj->set_data($field_name, $value);
        }
        return $obj;
    }

    public function get_sql_part($field_name, $field) {
        return "`".$field_name."` TIMESTAMP NOT NULL";
    }
}
