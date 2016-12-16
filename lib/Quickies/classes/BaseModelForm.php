<?php

namespace Iekadou\Quickies;

class BaseModelForm extends Renderable {
    protected $model = "Ieakdou\\Quickies\\BaseModel";
    protected $object_id;
    protected $fields = array();

    public function render() {
        $Model = $this->model;
        $Model = new $Model();

        $fields_to_render = array();
        foreach ($this->fields as $field_name) {
            $field = $this->fields[$field_name];
            array_push($fields_to_render, $field);
        }

    }
}
