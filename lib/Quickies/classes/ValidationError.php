<?php

namespace Iekadou\Quickies;

class ValidationError extends \Exception
{
    const _cn = "Iekadou\\Quickies\\ValidationError";

    protected $errors = array();

    public function __construct($errors) {
        $this->errors = $errors;
    }

    public function stringify() {
        if (empty($this->errors)) {
            return '{"error_msgs": [{"title": "'.Translation::translate('Sorry!').'", "message": "'.Translation::translate('Something went wrong!').'"}]}';
        }
        $error_output = "{";
        foreach ($this->errors as $error) {
            if ($error_output != "{") {
                $error_output .= ",";
            }
            if (is_array($error) == true) {
                $error_output .= '"'.$error[0].'": '.$error[1].'';
            } else {
                $error_output .= '"'.$error.'": "error"';
            }
        }
        $error_output .= "}";
        return $error_output;
    }
}
