<?php

namespace Iekadou\Quickies;

use Twig_Environment;
use Twig_Loader_Filesystem;

class BaseModelForm extends Renderable {
    const _cn = "Iekadou\\Quickies\\BaseModelForm";

    protected $model = "";
    protected $object_id;
    protected $fields = array();
    protected $template_name = '_include/_form.html';

    public function __construct($model, $fields, $object_id=null)
    {
        global $DB_CONNECTOR;
        if (!isset($DB_CONNECTOR)) {
            $DB_CONNECTOR = _i(DBConnector::_cn);
        }
        $this->db_connection = $DB_CONNECTOR;
        if ($this->db_connection->get_connect_errno()) {
            $this->errors[] = "db";
        }

        $loader = new Twig_Loader_Filesystem(PATH.'templates');
        if (TEMPLATE_CACHING) {
            $this->template = new Twig_Environment($loader, array(
                'cache' => PATH.'cached_templates',
            ));
        } else {
            $this->template = new Twig_Environment($loader, array());
        }
        $this->model = $model;
        $this->fields = $fields;
        $this->object_id = $object_id;
    }

    public function get_form_fields() {
        $render_fields = array();
        if (isset($this->object_id)) {
            $obj = _i($this->model)->get($this->object_id);
        } else {
            $obj = _i($this->model);
        }
        foreach($this->fields as $field_name) {
            if (isset($obj->fields[$field_name])) {
                $render_fields[$field_name]['opts'] = $obj->fields[$field_name];
                if ($obj->id) {
                    $render_fields[$field_name]['value'] = $obj->$field_name;
                }
                if (isset($render_fields[$field_name]['opts']['dependent_on'])) {
                    if (empty($render_fields[$field_name]['opts']['dependent_on'])) {
                        $render_fields[$field_name]['opts']['dependent_status'] = True;
                    }
                    elseif (empty($render_fields[$field_name]['dependent_value']) && !empty($render_fields[$field_name]['value'])) {
                        $render_fields[$field_name]['opts']['dependent_status'] = True;
                    }
                    elseif (!empty($render_fields[$field_name]['opts']['dependent_value']) && $render_fields[$field_name]['value'] == $render_fields[$field_name]['opts']['dependent_value']) {
                        $render_fields[$field_name]['opts']['dependent_status'] = True;
                    } else {
                        $render_fields[$field_name]['opts']['dependent_status'] = False;
                    }
                } else {
                    $render_fields[$field_name]['opts']['dependent_status'] = True;
                }
                if ($obj->fields[$field_name]['type'] == IntegerChoiceField::_cn) {
                    $render_fields[$field_name]['choices'] = _i($obj->fields[$field_name]['choices'])->get_values();
                }
                if ($obj->fields[$field_name]['type'] == ForeignKeyField::_cn) {
                    $array = array();
                    foreach(_i($obj->fields[$field_name]['foreign_type'])->filter_by() as $fk_obj) {
                        $array[$fk_obj->id] = $fk_obj->name;
                    }
                    $render_fields[$field_name]['choices'] = $array;

                }
                $render_fields[$field_name]['opts']['verbose_name'] = $obj->get_field_verbose_name($field_name);
            }
        }
        return $render_fields;
    }

    public function render($display=false) {
        $render_fields = $this->get_form_fields();
        $this->set_template_var('render_fields', $render_fields);
        $this->pre_render();
        if ($display) {
            echo $this->template->render($this->template_name, $this->get_template_vars());
        } else {
            return $this->template->render($this->template_name, $this->get_template_vars());
        }
    }
}
