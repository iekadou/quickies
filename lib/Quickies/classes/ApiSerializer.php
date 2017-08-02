<?php
namespace Iekadou\Quickies;

class ApiSerializer
{
    const _cn = "Iekadou\\Quickies\\ApiSerializer";

    public $model;
    public $fields = array();
    public $write_only_fields = array();
    public $read_only_fields = array();
    public $defined_serializers = array();
    public $filter_opts = array();
    public $sort_opts = array();
    public $source_fields = array();
    public $allowed_methods = array("GET", "PUT", "POST", "DELETE");
    public $serializer_instance_id = null;

    public function serialize($instances) {
        if (!is_array($instances)) {
            return $this->serialize_instance($instances);
        } else {
            $return = array();
            foreach($instances as $instance) {
                array_push($return, $this->serialize_instance($instance));
            }
            return $return;
        }
    }

    public function serialize_instance($instance) {
        $return = array();
        if (is_object($instance)) {
            $return["id"] = $instance->id;
        }
        foreach ($this->fields as $field) {
            if (!in_array($field, $this->write_only_fields)) {
                if (isset($this->source_fields[$field])) {
                    $function_name = $this->source_fields[$field];
                } else {
                    $field_name = $field;
                }
                if (isset($this->defined_serializers[$field])) {
                    if (isset($function_name)) {
                        $value = $instance->$function_name($field);
                    } else {
                        $value = $instance->$field_name;
                    }
                    $return[$field] = $this->defined_serializers[$field]->serialize($value);
                } else {
                    if (isset($function_name)) {
                        $value = $instance->$function_name($field);
                    } else {
                        if (isset($instance->$field_name)) {
                            $value = $instance->$field_name;
                        } else {
                            $value = null;
                        }
                    }
                    if (is_array($value) && array_values($value) === $value) {
                        $array = array();
                        foreach($value as $item) {
                            if (is_object($item)) {
                                array_push($array, $item->id);
                            } else {
                                array_push($array, $item);
                            }
                        }
                        $return[$field] = $array;
                    } else if (is_object($value)) {
                        $return[$field] = $value->id;
                    } else {
                        $return[$field] = $value;
                    }
                }
            }
        }
        if (isset($this->instance_template)) {
            $rendered_html = new Renderable($id=null, $name=null, $template=$this->instance_template);
            $rendered_html->set_template_var('instance', $instance);
            $return['rendered_html'] = $rendered_html->render(false);
        }
        return $return;
    }

    public function has_instance_rights($instance) {
        return true;
    }
}
