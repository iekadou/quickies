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

class ReflectedForeignKeyField extends Field {
    const _cn = "Iekadou\\Quickies\\ReflectedForeignKeyField";

    public function _get($obj, $field_name) {
        return _i($obj->fields[$field_name]['foreign_type'])->filter_by(array(array($obj->table.'_id', '=', $obj->id)));
    }
}

class ReflectedM2MField extends Field {
    const _cn = "Iekadou\\Quickies\\ReflectedM2MField";

    public function _get($obj, $field_name) {
        $foreign_obj_table = _i($obj->fields[$field_name]['foreign_type'])->table;
        $through_queryset = _i($obj->fields[$field_name]['through'])->filter_by(array(array($obj->table.'_id', '=', $obj->id)));
        $foreign_objs = array();
        foreach($through_queryset as $through_obj) {
            array_push($foreign_objs, $through_obj->$foreign_obj_table);
        }
        return $foreign_objs;
    }
}
