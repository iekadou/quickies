<?php
namespace Iekadou\Quickies;

class ForeignKeyField extends Field {
    const _cn = "Iekadou\\Quickies\\ForeignKeyField";

    public function _set($obj, $field_name, $value) {
        if (Utils::endsWith($field_name, '_id')) {
            global $DB_CONNECTOR;
            $obj->set_data($field_name, $DB_CONNECTOR->real_escape_string($value));
            return $obj;
        } else {
            return $obj;
        }
    }

    public function _validate_pre_db($obj, $field_name)
    {
        if (!isset($obj->fields[$field_name]['required']) && $obj->$field_name == null) {
            return true;
        }
        if (Utils::endsWith($field_name, '_id')) {
            if ($obj->$field_name) {
                return true;
            }
        }
        return false;
    }

    public function get_sql_part($field_name, $field) {
        return "`".$field_name."` int(11) NOT NULL";
    }
}
