<?php
namespace Iekadou\Quickies;

class UrlField extends Field {
    const _cn = "Iekadou\\Quickies\\UrlField";

    public function get_sql_part($field_name, $field) {
        $max_length = (isset($field['max_length'])) ? $field['max_length'] : 254;
        return "`".$field_name."` varchar(".$max_length.") NOT NULL";
    }
}