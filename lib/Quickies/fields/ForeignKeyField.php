<?php
namespace Iekadou\Quickies;

class ForeignKeyField extends Field {
    const _cn = "Iekadou\\Quickies\\ForeignKeyField";

    public function _set($obj, $field_name, $value) {
        if (Utils::endsWith($field_name, '_id')) {
            global $DB_CONNECTOR;
            $obj->set_data($field_name, $DB_CONNECTOR->real_escape_string(htmlentities($value, ENT_QUOTES)));
            return $obj;
        } else {
            return $obj;
        }
    }

    public function get_sql_part($field_name, $field) {
        return "`".$field_name."` int(11) NOT NULL";
    }
}
