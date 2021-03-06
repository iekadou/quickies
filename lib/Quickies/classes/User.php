<?php

namespace Iekadou\Quickies;

class User extends BaseModel
{
    const _cn = "Iekadou\\Quickies\\User";

    protected $table = 'user';
    protected $fields = array(
        'username' => array('type' => VarcharField::_cn, 'regex' => "/^[a-zA-Z0-9 ]{3,50}$/", 'unique' => true, 'min_length' => 3, 'max_length' => 32),
        'email' => array('type' => VarcharField::_cn, 'min_length' => 3, 'max_length' => 254, 'unique' => true),
        'password' => array('type' => VarcharField::_cn),
        'activated' => array('type' => BooleanField::_cn, 'default' => false),
        'activation_key' => array('type' => VarcharField::_cn, 'default' => '', 'max_length' => 254, 'min_length' => 3),
        'admin' => array('type' => BooleanField::_cn, 'default' => false)
    );
    protected $custom_fields = array();

    public $form_fields = array('username', 'email', 'activated', 'admin');

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
        $password = $this->db_connection->real_escape_string($password);
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $this->password = $hashed;
        return $this;
    }

    public function create()
    {
        if (!isset($this->errors) || empty($this->errors)) {
            if ($this->count_by() == 0) {
                $this->admin = True;
            }
            if (parent::create()) {
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
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $domain_name = $_SERVER['HTTP_HOST'];
        $site_root_url = $protocol.$domain_name;
        $subject = Translation::translate('Your account at {{ SITE_NAME }}', array('{{ SITE_NAME }}' => SITE_NAME));
        $content = Translation::translate("Hey {{ username }},
you can activate your account by clicking the following link:
{{ SITE_ROOT_URL }}{{ activate_url }}

Have fun on {{ SITE_NAME }}", array('{{ username }}' => $this->username, '{{ activation_key }}' => $this->activation_key, '{{ SITE_ROOT_URL }}' => $site_root_url, '{{ SITE_NAME }}' => SITE_NAME, '{{ activate_url }}' => UrlsPy::get_url('activate', array($this->activation_key,))));
        $header = 'From: '.NO_REPLY_EMAIL;
        if (mail($this->email, $subject, $content, $header)) {
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
