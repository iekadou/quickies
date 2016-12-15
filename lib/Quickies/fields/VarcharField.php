<?php
namespace Iekadou\Quickies;

class VarcharField extends Field {
    public function _validate($obj, $field_name, $value) {
        if (isset($obj->fields[$field_name]['regex'])) {
            if (!preg_match($obj->fields[$field_name]['regex'], $value)) {
                $obj->errors[] = $field_name;
                return false;
            }
        }
        if (isset($obj->fields[$field_name]['max_length'])) {
            if (strlen($value) > $obj->fields[$field_name]['max_length']) {
                return false;
            }
        }
        if (isset($obj->fields[$field_name]['min_length'])) {
            if (strlen($value) < $obj->fields[$field_name]['min_length']) {
                return false;
            }
        }
        return true;
    }
}
