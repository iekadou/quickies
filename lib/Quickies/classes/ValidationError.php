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
        header("HTTP/1.0 400 Not Found");
        if (empty($this->errors)) {
            return '{"error_msgs": [{"title": "'.Translation::translate('Sorry!').'", "message": "'.Translation::translate('Something went wrong!').'"}]}';
        }
        $error_output = array();
        foreach ($this->errors as $error) {
            if (is_array($error) == true) {
                $error_output[$error[0]] = $error[1];
            } else {
                $error_output[$error] = "error";
            }
        }
        return json_encode($error_output);
    }
}
