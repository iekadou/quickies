<?php
namespace Iekadou\Quickies;

class IntegerField extends Field {
    const _cn = "Iekadou\\Quickies\\IntegerField";

    public function get_sql_part($field_name, $field) {
        return "`".$field_name."` int(15) NOT NULL";
    }
}