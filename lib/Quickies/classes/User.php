<?php

namespace Iekadou\Quickies;

class User extends BaseModel
{
    protected $table = 'user';
    protected $fields = array(
        'username' => array('type' => "Iekadou\\Quickies\\VarcharField", 'regex' => "/^[a-zA-Z0-9 ]{3,50}$/", 'unique' => true, 'min_length' => 3, 'max_length' => 10),
        'email' => array('type' => "Iekadou\\Quickies\\VarcharField", 'min_length' => 3, 'max_length' => 254),
        'password' => array('type' => "Iekadou\\Quickies\\VarcharField"),
        'activated' => array('type' => "Iekadou\\Quickies\\BooleanField", 'default' => false),
        'activation_key' => array('type' => "Iekadou\\Quickies\\VarcharField", 'default' => '', 'max_length' => 254, 'min_length' => 3),
        'admin' => array('type' => "Iekadou\\Quickies\\BooleanField", 'default' => false)
    );
    protected $custom_fields = array();

    protected function _pre_construct() {
        foreach($this->custom_fields as $custom_field_name => $custom_field) {
            $this->fields[$custom_field_name] = $custom_field;
        }
    }

    public function activate() {
        $this->activated = true;
        return $this;
    }

    public function deactivate() {
        $this->activated = false;
        return $this;
    }

    public function set_new_password($password)
    {
        $password = $this->db_connection->real_escape_string(htmlentities($password, ENT_QUOTES));
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $this->password = $hashed;
        return $this;
    }

    public function register_new_user($username, $email, $password)
    {
        $this->email = $email;
        $this->username = $username;
        $this->password = $password;

        if (!isset($this->errors) || empty($this->errors)) {
            if ($this->create()) {
                $this->id = $this->db_connection->get_insert_id();
                if ($this->send_activation_email()) {
                    return $this;
                } else {
                    $this->errors[] = 'activation';
                }
            }
        }
        throw new ValidationError($this->errors);
    }

    private function generate_activation_key() {
        $activation_key = '';
        while ($activation_key == '') {
            for($length = 0; $length < 20; $length++) {
                $chr_cat = rand(0,1);
                switch ($chr_cat) {
                    case 0:
                        $char = chr(rand(50,57));
                        break;
                    default:
                        $char = chr(rand(97,122));
                }
                $activation_key .= $char;
            }
            $query_activation_key = $this->db_connection->query("SELECT * FROM user WHERE activation_key = '" . $activation_key . "';");
            if ($query_activation_key->num_rows > 0) {
                $activation_key = '';
            }
        }
        $this->activation_key = $activation_key;
    }
    
    public function send_activation_email() {
        $this->generate_activation_key();
        $this->save();
        $subject = Translation::translate('Your account at {{ SITE_NAME }}', array('{{ SITE_NAME }}' => SITE_NAME));
        $content = Translation::translate("Hey {{ username }},
you can activate your account by clicking the following link:
http://{{ DOMAIN }}{{ activate_url }}

Have fun on {{ SITE_NAME }}", array('{{ username }}' => $this->get_username(), '{{ activation_key }}' => $this->get_activation_key(), '{{ SITE_NAME }}' => SITE_NAME, '{{ DOMAIN }}' => DOMAIN, '{{ activate_url }}' => UrlsPy::get_url('activate', array($this->get_activation_key(),))));
        $header = 'From: '.NO_REPLY_EMAIL;
        if (mail($this->get_email(), $subject, $content, $header)) {
            return $this;
        } else {
            throw new ValidationError(array());
        }
    }
    
    public function send_new_password() {

        $new_password = '';
        for($length = 0; $length < 20; $length++) {
            $chr_cat = rand(0,1);
            switch ($chr_cat) {
                case 0:
                    $char = chr(rand(50,57));
                    break;
                default:
                    $char = chr(rand(97,122));
            }
            $new_password .= $char;
        }
        
        $subject = Translation::translate('Your new password at {{ SITE_NAME }}', array('{{ SITE_NAME }}' => SITE_NAME));
        $content = Translation::translate('Hey {{ username }},
your new password is: {{ new_password }}

Have fun on {{ SITE_NAME }}', array('{{ SITE_NAME }}' => SITE_NAME, '{{ username }}' => $this->username, '{{ new_password }}' => $new_password));
        $header = 'From: '.NO_REPLY_EMAIL;
        if (mail($this->email, $subject, $content, $header)) {
            $this->set_new_password($new_password);
            $this->save();
            return $this;
        } else {
            throw new ValidationError(array());
        }
    }

    public function interpret_request($POST, $FILES, $fields=false)
    {
        $this->reset_errors();
        foreach($this->fields as $field_name => $field) {
            if (!$fields || in_array($field_name, $fields)) {
                if ($field_name == 'password') {
                    if (isset($POST[$field_name])) {
                        $this->set_new_password($POST[$field_name]);
                    }
                } else {
                    if (isset($POST[$field_name])) {
                        $this->$field_name = $POST[$field_name];
                    } else {
                        $this->$field_name = null;
                    }
                }
            }
        }
        if (!empty($this->errors)) {
            throw new ValidationError($this->errors);
        }
        return $this;
    }

}
