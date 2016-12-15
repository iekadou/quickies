<?php
namespace Iekadou\Quickies;

class Choices
{
    protected static $choices = array();

    public static function getKeys() {
        $class = get_called_class();
        return array_keys($class::$choices);
    }

    public static function getValues() {
        $class = get_called_class();
        $return = array();
        foreach(array_values($class::$choices) as $value) {
            array_push($return, $value);
        }
        return $return;
    }

    public static function get_by_name($name)
    {
        $class = get_called_class();
        foreach ($class::$choices as $key => $value) {
            if ($value==$name) {
                return $key;
            }
        }
        return false;
    }

    public static function get_by_id($id)
    {
        $class = get_called_class();
        if (isset($class::$choices[$id])) {
            return $class::$choices[$id];
        }
        return false;

    }

}
