<?php
namespace Iekadou\Quickies;

class IntegerChoiceField extends Field
{
    const _cn = "Iekadou\\Quickies\\IntegerChoiceField";

    public function _set($obj, $field_name, $value)
    {
        if (isset($obj->fields[$field_name]['choices'])) {
        $choices = _i($obj->fields[$field_name]['choices']);
        if ($choices->get_by_id($value)) {
            $obj->$field_name = $value;
        } else {
            $obj->errors[] = $field_name;
        }
        } else {
            $obj->$field_name = $value;
        }
        return $obj;
    }

    public function _get($obj, $field_name)
    {
        return $obj->get_data($field_name);
    }

    public function get_sql_part($field_name, $field) {
        return "`".$field_name."` int(5) NOT NULL";
    }
}
