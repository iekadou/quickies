<?php
namespace Iekadou\Quickies;

class Field {
    const _cn = "Iekadou\\Quickies\\Field";

    public function _get($obj, $field_name) {
        return $obj->get_data($field_name);
    }
    public function _set($obj, $field_name, $value) {
        if ($this->_validate($obj, $field_name, $value)) {
            $obj->set_data($field_name, $value);
        } else {
            $obj->errors[] = $field_name;
        }
        return $obj;
    }
    protected function _validate($obj, $field_name, $value) {
        return true;
    }
    public function _validate_pre_db($obj, $field_name) {
        if (isset($obj->fields[$field_name]['unique'])) {
            if (_i($obj::_cn)->count_by(array(array($field_name, '=', $obj->$field_name), array('id', '!=', $obj->id))) > 0) {
                return false;
            }
        }
        return true;
    }

    public function get_sql_part($field_name, $field) {
        return "";
    }

}
