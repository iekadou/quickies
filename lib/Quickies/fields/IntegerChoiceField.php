<?php
namespace Iekadou\Quickies;

class IntegerChoiceField extends Field {
    public function _set($obj, $field_name, $value) {
        if (isset($obj->fields[$field_name]['choices'])) {
            $choices = $obj->fields[$field_name]['choices'];
            if ($choices::get_by_id($value)) {
                $obj->$field_name = $value;
            } else {
                $obj->errors[] = $field_name;
            }
        }
        return $obj;
    }
    public function _get($obj, $field_name) {
        return $obj->get_data($field_name);
    }
//    public function _get($obj, $field_name) {
//        if (isset($obj->fields[$field_name]['choices'])) {
//            $obj->fields[$field_name]['choices'].$value;
//        }
//        return $obj;
//    }
}