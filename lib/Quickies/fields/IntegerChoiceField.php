<?php
namespace Iekadou\Quickies;

class IntegerChoiceField extends Field
{
    const _cn = "Iekadou\\Quickies\\IntegerChoiceField";

    public function _set($obj, $field_name, $value)
    {
        $choices = _i($obj->fields[$field_name]['choices']);
        if ($choices->get_by_id($value)) {
            $obj->$field_name = $value;
        } else {
            $obj->errors[] = $field_name;
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
