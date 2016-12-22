<?php
namespace Iekadou\Quickies;

class FileField extends Field {
    const _cn = "Iekadou\\Quickies\\FileField";

    public function get_sql_part($field_name, $field) {
        $max_length = (isset($field['max_length'])) ? $field['max_length'] : 254;
        return "`".$field_name."` varchar(".$max_length.") NOT NULL";
    }
}
