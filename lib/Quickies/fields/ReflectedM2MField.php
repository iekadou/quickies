<?php
namespace Iekadou\Quickies;

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
