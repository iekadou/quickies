<?php
namespace Iekadou\Quickies;

class ApiView
{
    const _cn = "Iekadou\\Quickies\\ApiView";

    static private $serializer;

    public function __construct($serializer)
    {
        header("Access-Control-Allow-Headers: APNKEY");
        header('Content-Type: application/json');
        self::$serializer = $serializer;
    }

    public static function render() {
        try {
            if (!in_array(REQUEST_METHOD, self::$serializer->allowed_methods)) {
                Utils::raise404_api();
                die();
            }
            if (!isset(self::$serializer->serializer_instance_id)) {
                if (isset($_GET['id'])) {
                    self::$serializer->serializer_instance_id = $_GET['id'];
                }
                if (isset($_POST['id'])) {
                    self::$serializer->serializer_instance_id = $_POST['id'];
                }
            }
            $writable_fields = array();
            foreach(self::$serializer->fields as $field) {
                if (!in_array($field, self::$serializer->read_only_fields)) {
                    array_push($writable_fields, $field);
                }
            }
            switch (REQUEST_METHOD){
                case "POST":
                    if (!self::$serializer->serializer_instance_id) {
                        $instance = self::$serializer->model->interpret_request($_POST, $_FILES, $writable_fields);
                        $instance = $instance->create();
                        return json_encode(self::$serializer->serialize($instance));
                    }
                    break;
                case "PUT":
                    if (self::$serializer->serializer_instance_id) {
                        $instance = self::$serializer->model->get(self::$serializer->serializer_instance_id);
                        if (!$instance || !self::$serializer->has_instance_rights($instance)) {
                            Utils::raise404_api();
                            die();
                        }
                        $POST = $_POST;
                        foreach (self::$serializer->fields as $field) {
                            if ($instance->fields[$field]['type'] == BooleanField::_cn && !in_array($field, self::$serializer->read_only_fields) && !isset($POST[$field])) {
                                $POST[$field] = false;
                            }
                        }
                        $instance = $instance->interpret_request($POST, $_FILES, $writable_fields);
                        $instance = $instance->save();
                        return json_encode(self::$serializer->serialize($instance));
                    }
                    break;
                case "DELETE":
                    if (self::$serializer->serializer_instance_id) {
                        $instance = self::$serializer->model->get(self::$serializer->serializer_instance_id);
                        if (!$instance || !self::$serializer->has_instance_rights($instance)) {
                            Utils::raise404_api();
                            die();
                        }
                        $instance->delete();
                        self::$serializer->serializer_instance_id = null;
                        return json_encode(self::$serializer->serialize($instance));
                    }
                    break;
                case "GET":
                    if (self::$serializer->serializer_instance_id) {
                        $instance = self::$serializer->model->get(self::$serializer->serializer_instance_id);
                        if (!$instance || !self::$serializer->has_instance_rights($instance)) {
                            Utils::raise404_api();
                            die();
                        }
                        return json_encode(self::$serializer->serialize($instance));
                    } else {
                        $instances = self::$serializer->model->filter_by(self::$serializer->filter_opts, self::$serializer->sort_opts);
                        $allowed_instances = array();
                        foreach($instances as $instance) {
                            if (self::$serializer->has_instance_rights($instance)) {
                                array_push($allowed_instances, $instance);
                            }
                        }
                        return json_encode(self::$serializer->serialize($allowed_instances));
                    }
                    break;
                default:
                    throw new ValidationError(array());
            }
        } catch (ValidationError $e) { echo $e->stringify(); die(); }
        return false;
    }
}
