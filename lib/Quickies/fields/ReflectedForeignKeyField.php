<?php
namespace Iekadou\Quickies;

class ReflectedForeignKeyField extends Field {
    const _cn = "Iekadou\\Quickies\\ReflectedForeignKeyField";

    public function _get($obj, $field_name) {
        return _i($obj->fields[$field_name]['foreign_type'])->filter_by(array(array($obj->table.'_id', '=', $obj->id)));
    }
}