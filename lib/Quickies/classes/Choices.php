<?php
namespace Iekadou\Quickies;

class Choices
{
    const _cn = "Iekadou\\Quickies\\Choices";

    protected static $choices = array();

    public static function get_keys() {
        $class = get_called_class();
        $return = array();
        foreach($class::$choices as $key => $value) {
            $return[$value[0]] = $key;
        }
        return $return;
    }

    public function get_keys_dynamic() {
        $class = get_called_class();
        $return = array();
        foreach($class::$choices as $key => $value) {
            $return[$value[0]] = $key;
        }
        return $return;
    }

    public static function get_values() {
        $class = get_called_class();
        return $class::$choices;
    }

    public static function get_by_name($name)
    {
        $class = get_called_class();
        foreach ($class::$choices as $key => $value) {
            if ($value[0] == $name) {
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
