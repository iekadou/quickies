<?php
namespace Iekadou\Quickies;

class TimestampField extends Field {
    const _cn = "Iekadou\\Quickies\\TimestampField";

    public function get_sql_part($field_name, $field) {
        return "`".$field_name."` TIMESTAMP NOT NULL";
    }
}