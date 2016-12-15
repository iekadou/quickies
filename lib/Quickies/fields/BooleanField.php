<?php
namespace Iekadou\Quickies;

class BooleanField extends Field {

    public function _get($obj, $field_name) {
        if ($obj->get_data($field_name)) {
            return $obj->get_data($field_name) == true || $obj->get_data($field_name) == 1;
        }
        return false;
    }

    public function _set($obj, $field_name, $value) {
        if ($value == 'true' || $value == 'on' || $value == true || $value == 1) {
            $obj->set_data($field_name, true);
        } else {
            $obj->set_data($field_name, false);
        }
        return $obj;
    }

}