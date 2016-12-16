<?php
namespace Iekadou\Quickies;

class TextField extends Field {
    const _cn = "Iekadou\\Quickies\\TextField";

    public function get_sql_part($field_name, $field) {
        return "`".$field_name."` text NOT NULL";
    }
}