<?php
namespace Iekadou\Quickies;

abstract class BaseModel
{
    const _cn = "Iekadou\\Quickies\\BaseModel";

    protected $data = array();
    protected $db_connection = null;
    protected $id = null;
    protected $fields = array();
    protected $cached_fields = array();
    public $errors = null;

    protected function _pre_construct()
    {
    }

    public function __construct()
    {
        $this->_pre_construct();
        global $DB_CONNECTOR;
        if (!isset($DB_CONNECTOR)) {
            $DB_CONNECTOR = new DBConnector();
        }
        $this->db_connection = $DB_CONNECTOR;
        if ($this->db_connection->get_connect_errno()) {
            $this->errors[] = "db";
        }
        foreach($this->fields as $field_name => $field) {
            if (isset($field['default'])) {
                $this->data[$field_name] = $field['default'];
            } else {
                $this->data[$field_name] = "";
            }
        }
        $this->data['deleted_files'] = array();
    }

    public function __isset($field_name) {
        return $field_name == '_cn' || isset($this->id) || isset($this->data[$field_name]) || isset($this->data[$field_name.'_id']) || method_exists($this, 'get_'.$field_name) ||
            ((strpos($field_name, '_display') > 0) && isset($this->data[substr($field_name, 0, strpos($field_name, '_display'))])) ||
            ((strpos($field_name, '_file_list') > 0) && isset($this->data[substr($field_name, 0, strpos($field_name, '_file_list'))]));
    }

    public function __get($field_name) {
        if ($field_name == '_cn') {
            return self::_cn;
        }
        if ($field_name == 'id') {
            return $this->id;
        }
        if ($field_name == in_array($field_name, array_keys($this->fields)) || method_exists($this, 'get_'.$field_name)) {
            $method_name = 'get_' . $field_name;
            if (method_exists($this, $method_name)) {
                return $this->$method_name();
            }
            $field = $this->fields[$field_name]['type'];
            if ($field == "Text") {
                return;
            } else {
                $field = new $field($this->fields[$field_name]);
            }
            return $field->_get($this, $field_name);
        } else if (!empty($field_name) && $field_name == in_array($field_name.'_id', array_keys($this->fields))) {
            $field_name .= '_id';
            $method_name = 'get_' . $field_name;
            if (method_exists($this, $method_name)) {
                return $this->$method_name();
            }
            $field = $this->fields[$field_name]['type'];
            $field = new $field($this->fields[$field_name]);
            $id = $field->_get($this, $field_name);
            if (DB_CACHING) {
                if (isset($this->cached_fields[$field_name])) {
                    return $this->cached_fields[$field_name];
                }
            }
            $result = _i($this->fields[$field_name]['foreign_type'])->get($id);
            if (DB_CACHING) {
                $this->cached_fields[$field_name] = $result;
            }
            return $result;
        } else if (strpos($field_name, '_display') > 0) {
            $field_name = substr($field_name, 0, strpos($field_name, '_display'));
            $choices = _i($this->fields[$field_name]['choices']);
            return $choices::get_by_id($this->$field_name)[1];
        } else if (strpos($field_name, '_file_list') > 0) {
            $field_name = substr($field_name, 0, strpos($field_name, '_file_list'));
            $files = preg_split('/;/', $this->$field_name);
            if ($files[0] == '') {
                return array();
            }
            $result = array();
            foreach ($files as $file) {
                $re = '/\[(?P<file>[\s\S]+)\](?P<path>[\s\S]+)/';
                preg_match($re, $file, $matches);
                $result[] = array("file" => $matches['file'], "path" => $matches['path']);
            }
            return $result;
        } else {
            return $this->$field_name;
        }
    }

    public function __set($field_name, $value) {

        if (isset($value)) {
            if ($field_name == in_array($field_name, array_keys($this->fields)) || $field_name . '_id' == in_array($field_name, array_keys($this->fields))) {
                $method_name = 'set_' . $field_name;
                if (method_exists($this, $method_name)) {
                    return $this->$method_name($value);
                }
                $field = $this->fields[$field_name]['type'];
                if ($field != "Text") {
                    $field = new $field($this->fields[$field_name]);
                    $field->_set($this, $field_name, $value);
                }
                return $this;
            } else {
                $this->$field_name = $value;
                return $this;
            }
        }
        return $this;
    }

    public function makemigrations() {
        $new_fields = array();
        $del_fields = array();
        $change_fields = array();
        $dir = new \DirectoryIterator(PATH.'vendor/iekadou/quickies/lib/Quickies/migrations/');
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()) {
                $filename = $fileinfo->getFilename();
                if (!file_exists(PATH.'migrations/'.$filename)) {
                    copy(PATH.'vendor/iekadou/quickies/lib/Quickies/migrations/'.$filename, PATH.'migrations/'.$filename);
                }
            }
        }

        $dir = new \DirectoryIterator(PATH.'migrations/');
        $latest_migration = 0;
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()) {
                $filename = $fileinfo->getFilename();
                $regex = "/^".$this->table."_([0-9])+.php$/";
                if (preg_match($regex, $filename)) {
                    $version_number = substr($filename, strlen($this->table)+1, strlen($filename) - strlen($this->table) - strlen('.php')-1);
                    if ($version_number > $latest_migration) {
                        $latest_migration = $version_number;
                    }
                }
            }
        }
        if ($latest_migration > 0) {
            $latest_migration_name = PATH.'migrations/'.$this->table.'_'.$latest_migration.'.php';
            include($latest_migration_name);
            foreach($this->fields as $field_name => $field) {
                if ($field['type'] != ReflectedForeignKeyField::_cn && $field['type'] != ReflectedM2MField::_cn) {
                    if (!isset($migration['fields'][$field_name])) {
                        $new_fields[$field_name] = $this->fields[$field_name];
                    } else {
                        $changes = $this->_detect_field_changes($this->fields[$field_name], $migration['fields'][$field_name]);
                        if (!empty($changes)) {
                            $change_fields[$field_name] = $changes;
                        }
                    }
                }
            }
            foreach($migration['fields'] as $field_name => $field) {
                if ($field['type'] != ReflectedForeignKeyField::_cn && $field['type'] != ReflectedM2MField::_cn) {
                    if (!isset($this->fields[$field_name])) {
                        $del_fields[$field_name] = $migration['fields'][$field_name];
                    }
                }
            }
        } else {
            $new_fields = $this->fields;
        }
        $current_migration = $latest_migration + 1;
        $query_parts = array();
        include(PATH.'/config/secrets.php');
        if ($latest_migration == 0) {
            array_push($query_parts, "CREATE TABLE `".$secrets['db_name']."`.`".$this->table."` (`id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY");
            foreach ($new_fields as $field_name => $field) {
                if ($this->_get_field_query($field_name, $field) != "") {
                    array_push($query_parts, ", ".$this->_get_field_query($field_name, $field));
                }
            }
            array_push($query_parts, ");");
        } else {
            if (!empty($new_fields)) {
                foreach($new_fields as $field_name => $field) {
                    array_push($query_parts, "ALTER TABLE `".$secrets['db_name']."`.`".$this->table."` ADD ".$this->_get_field_query($field_name, $field).";");
                }
            }
            if (!empty($del_fields)) {
                foreach($del_fields as $field_name => $field) {
                    array_push($query_parts, "ALTER TABLE `".$secrets['db_name']."`.`".$this->table."` DROP COLUMN `".$field_name."`;");
                }
            }
            if (!empty($change_fields)) {
                foreach($change_fields as $field_name => $field) {
                    array_push($query_parts, "ALTER TABLE `".$secrets['db_name']."`.`".$this->table."` CHANGE `".$field_name."` ".$this->_get_field_query($field_name, $field).";");
                }
            }
        }
        unset($secrets);

        if (empty($new_fields) && empty($del_fields) && empty($change_fields)) {
        } else {
            $complete_query = "";
            foreach ($query_parts as $part) {
                $complete_query .= $part;
            }
            $current_migration_filename = PATH . '/migrations/' . $this->table . '_' . $current_migration . '.php';
            $string = '<?php
$migration = array();
$migration[\'id\']  =  "' . $this->table . '_' . $current_migration . '";
$migration[\'query\'] = "' . $complete_query . '";
$migration[\'fields\'] = ' . var_export($this->fields, true) . ';';
            file_put_contents($current_migration_filename, $string);
            print "Migrations created at: ".$this->table."\n";
        }
    }

    protected function _get_field_query($field_name, $field) {
        if (class_exists($field['type'])) {
            return _i($field['type'])->get_sql_part($field_name, $field);
        }
    }

    protected function _detect_field_changes($field1, $field2) {
        $changes = array();
        if ($field1['type'] != $field2['type']) {
            return $field1;
        }
        foreach ($field1 as $key => $value) {
            if (!isset($field2[$key])) {
                $changes[$key] = $value;
            } else {
                if ($field1[$key] != $field2[$key]) {
                    if (($field1['type'] == "Iekadou\\Quickies\\PasswordField" && $key == 'max_length') ||
                        ($field1['type'] == "Iekadou\\Quickies\\UrlField" && $key == 'max_length') ||
                        ($field1['type'] == VarcharField::_cn && $key == 'max_length') ||
                        ($field1['type'] == TimestampField::_cn && $key == 'auto_create') ||
                        ($field1['type'] == TimestampField::_cn && $key == 'auto_update') ||
                        ($field1['type'] == ForeignKeyField::_cn && $key == 'foreign_type') ||
                        ($field1['type'] == IntegerChoiceField::_cn && $key == 'choices') ||
                        $key == 'required' ||
                        $key == 'default') {
                        $changes[$key] = $value;
                    }
                }
            }
        }
        if (empty($changes)) {
            return array();
        }
        return $field1;
    }

    protected function reset_errors() {
        $this->errors = null;
    }

    public function get_data($field_name)
    {
        return $this->data[$field_name];
    }

    public function set_data($field_name, $value)
    {
        $this->data[$field_name] = $value;
        return $this;
    }

    public function get($id)
    {
        $this->reset_errors();
        $id = $this->db_connection->real_escape_string($id);
        $obj_query = $this->db_connection->query("SELECT * FROM ".$this->table." WHERE id = '" . $id . "';");
        if ($obj_query->num_rows == 1) {
            $obj = $obj_query->fetch_object();
            foreach($this->fields as $field_name => $field) {
                if ($field['type'] != ReflectedForeignKeyField::_cn && $field['type'] != ReflectedM2MField::_cn) {
                    $this->$field_name = Utils::mysql_unescape_string($obj->$field_name);
                }
            }
            $this->id = $obj->id;
            return $this;
        }
        return false;
    }

    public function save()
    {
        $this->reset_errors();
        $update_str = '';
        $i = 0;
        foreach($this->fields as $field_name => $field) {
            if ($field['type'] != ReflectedForeignKeyField::_cn && $field['type'] != ReflectedM2MField::_cn) {
                if (!_i($this->fields[$field_name]['type'])->_validate_pre_db($this, $field_name)) {
                    $this->errors[] = $field_name;
                }
                if ($this->fields[$field_name]['type'] == TimestampField::_cn && isset($this->fields[$field_name]['auto_update']) && $this->fields[$field_name]['auto_update']) {
                    $this->$field_name = time();
                }
                if ($i > 0) {
                    $update_str .= ", ";
                }
                $i++;
                if ($this->fields[$field_name]['type'] == BooleanField::_cn) {
                    $update_str .= $field_name . " = '" . ($this->$field_name ? 1 : 0) . "'";
                } else {
                    $update_str .= $field_name . " = '" . $this->db_connection->real_escape_string($this->$field_name) . "'";
                }
            }
        }
        if (!empty($this->errors)) {
            throw new ValidationError($this->errors);
        }
        $obj_query = $this->db_connection->query("UPDATE ".$this->table." set ".$update_str." WHERE id = '" . $this->id . "';");
        if ($obj_query) {
            if (isset($this->data['deleted_files'])) {
                foreach ($this->data['deleted_files'] as $deleted_file) {
                    if (file_exists($deleted_file)) {
                        unlink($deleted_file);
                    }
                }
                $this->data['deleted_files'] = array();
            }
            return $this;
        }
        return false;
    }

    public function create()
    {
        $this->reset_errors();
        $insert_str = '(';
        $i = 0;
        foreach($this->fields as $field_name => $field) {
            if ($field['type'] != ReflectedForeignKeyField::_cn && $field['type'] != ReflectedM2MField::_cn) {
                if (!_i($this->fields[$field_name]['type'])->_validate_pre_db($this, $field_name)) {
                    $this->errors[] = $field_name;
                }
                if ($i > 0) {
                    $insert_str .= ", ";
                }
                $i++;
                $insert_str .= $field_name;
            }
        }
        $insert_str .= ') VALUES (';
        $i = 0;
        foreach($this->fields as $field_name => $field) {
            if ($field['type'] != ReflectedForeignKeyField::_cn && $field['type'] != ReflectedM2MField::_cn) {
                if ($i > 0) {
                    $insert_str .= ", ";
                }
                $i++;
                if ($this->fields[$field_name]['type'] == TimestampField::_cn && isset($this->fields[$field_name]['auto_create']) && $this->fields[$field_name]['auto_create']) {
                    $this->$field_name = time();
                }
                if (!isset($this->$field_name)) {
                    $this->$field_name = '';
                }
                if ($this->fields[$field_name]['type'] == BooleanField::_cn) {
                    $insert_str .= "'".($this->$field_name ? 1 : 0)."'";
                } else {
                    $insert_str .= "'".$this->db_connection->real_escape_string($this->$field_name )."'";
                }
            }
        }

        $insert_str .= ')';
        if (!empty($this->errors)) {
            throw new ValidationError($this->errors);
        }
        $obj_query = $this->db_connection->query("INSERT INTO ".$this->table." ".$insert_str.";");
        if ($obj_query) {
            $this->id = $this->db_connection->get_insert_id();
            return $this;
        }

        echo $this->db_connection->get_error();
        return false;
    }

    public function delete($id=null)
    {
        $this->reset_errors();
        if ($id) {
            $id = $this->db_connection->real_escape_string($id, ENT_QUOTES);
        } else {
            $id = $this->id;
        }
        if ($id) {
            $user_query = $this->db_connection->query("DELETE FROM ".$this->table." WHERE id = '" . $id . "';");
            if ($user_query) {
                foreach($this->fields as $field_name => $field) {
                    if ($this->fields[$field_name]['type'] == FileField::_cn) {
                        if (file_exists(PATH.$this->$field_name) && is_file(PATH.$this->$field_name)) {
                            unlink(PATH.$this->$field_name);
                        }
                    }
                }
                return true;
            }
        }
        return false;
    }

    public function delete_by($conditions)
    {
        $this->reset_errors();
        $condition_str = '';
        $i = 0;
        foreach($conditions as $condition) {
            if ($condition[0] == 'id' || in_array($condition[0], array_keys($this->fields))) {
                if ($i > 0) {
                    $condition_str .= ' and ';
                } else {
                    $condition_str = "WHERE ";
                }
                $i++;
                $condition_str .= $condition[0]." ".$condition[1]." '".$this->db_connection->real_escape_string($condition[2])."'";
            }
        }
        if ($i > 0) {
            $obj_delete = $this->db_connection->query("DELETE FROM ".$this->table." ".$condition_str.";");
            return $obj_delete != false;
        } else {
            return false; // TODO hint: do not delete all by accident
        }

    }

    public function count_by($conditions=array(), $sortings=array())
    {
        $this->reset_errors();
        $condition_str = '';
        $i = 0;
        foreach($conditions as $condition) {
            if ($condition[0] == 'id' || in_array($condition[0], array_keys($this->fields))) {
                if ($i > 0) {
                    $condition_str .= ' and ';
                } else {
                    $condition_str = "WHERE ";
                }
                $i++;
                $condition_str .= $condition[0]." ".$condition[1]." '".$this->db_connection->real_escape_string($condition[2])."'";
            }
        }
        $order_str = '';
        $i = 0;
        foreach($sortings as $sorting) {
            if ($sorting[0] == 'id' || in_array($sorting[0], array_keys($this->fields))) {
                if ($i > 0) {
                    $order_str .= ', ';
                } else {
                    $order_str = 'ORDER BY ';
                }
                $i++;
                $order_str .= $sorting[0]." ".$sorting[1];
            }
        }
        $obj_count = $this->db_connection->query("SELECT count(*) as count FROM ".$this->table." ".$condition_str." ".$order_str.";");
        $obj = $obj_count->fetch_object();
        return $obj->count;
    }

    public function get_by($conditions, $sortings=array(), $limit='')
    {
        $this->reset_errors();
        $condition_str = '';
        $i = 0;
        foreach($conditions as $condition) {
            if ($condition[0] == 'id' || in_array($condition[0], array_keys($this->fields))) {
                if ($i > 0) {
                    $condition_str .= ' and ';
                } else {
                    $condition_str = "WHERE ";
                }
                $i++;
                $condition_str .= $condition[0]." ".$condition[1]." '".$this->db_connection->real_escape_string($condition[2])."'";
            }
        }
        $order_str = '';
        $i = 0;
        foreach($sortings as $sorting) {
            if ($sorting[0] == 'id' || in_array($sorting[0], array_keys($this->fields))) {
                if ($i > 0) {
                    $order_str .= ', ';
                } else {
                    $order_str = 'ORDER BY ';
                }
                $i++;
                $order_str .= $sorting[0]." ".$sorting[1];
            }
        }
        $limit_str = "";
        if (!empty($limit)) {
            $limit_str = "LIMIT ".$this->db_connection->real_escape_string($limit);
        }
        $obj_query = $this->db_connection->query("SELECT * FROM ".$this->table." ".$condition_str." ".$order_str." ".$limit_str.";");
        if ($obj_query && $obj_query->num_rows == 1) {
            $obj = $obj_query->fetch_object();
            foreach($this->fields as $field_name => $field) {
                if ($field['type'] != ReflectedForeignKeyField::_cn && $field['type'] != ReflectedM2MField::_cn) {
                    $this->$field_name = Utils::mysql_unescape_string($obj->$field_name);
                }
            }
            $this->id = $obj->id;
            return $this;
        }
        return false;
    }

    public function filter_by($conditions=array(), $sortings=array(), $limit='')
    {
        $this->reset_errors();
        $condition_str = '';
        $i = 0;
        foreach($conditions as $condition) {
            if ($condition[0] == 'id' || in_array($condition[0], array_keys($this->fields))) {
                if ($i > 0) {
                    $condition_str .= ' and ';
                } else {
                    $condition_str = "WHERE ";
                }
                $i++;
                $condition_str .= $condition[0]." ".$condition[1]." '".$this->db_connection->real_escape_string($condition[2])."'";
            }
        }
        $order_str = '';
        $i = 0;
        foreach($sortings as $sorting) {
            if ($sorting[0] == 'id' || in_array($sorting[0], array_keys($this->fields))) {
                if ($i > 0) {
                    $order_str .= ', ';
                } else {
                    $order_str = 'ORDER BY ';
                }
                $i++;
                $order_str .= $sorting[0]." ".$sorting[1];
            }
        }
        $limit_str = "";
        if (!empty($limit)) {
            $limit_str = "LIMIT ".$this->db_connection->real_escape_string($limit);
        }
        $obj_array = Array();
        $obj_query = $this->db_connection->query("SELECT * FROM ".$this->table." ".$condition_str." ".$order_str." ".$limit_str.";");
        if ($obj_query) {
            while ($row = $obj_query->fetch_object()) {
                $obj = _i(get_class($this));
                foreach($this->fields as $field_name => $field) {
                    if ($field['type'] != ReflectedForeignKeyField::_cn && $field['type'] != ReflectedM2MField::_cn) {
                        $obj->$field_name = Utils::mysql_unescape_string($row->$field_name);
                    }
                }
                $obj->id = $row->id;
                $obj_array[] = $obj;

            }
            return $obj_array;
        }
        return false;
    }


    public function interpret_request($POST, $FILES, $fields=false)
    {
        $this->reset_errors();
        foreach($this->fields as $field_name => $field) {
            if (!$fields || in_array($field_name, $fields)) {
                if (isset($POST[$field_name])) {
                    if ($this->fields[$field_name]['type'] != FileField::_cn) {
                        $this->$field_name = $POST[$field_name];
                    } else {
                        $files_str = "";
                        if (is_array($POST[$field_name])) {
                            $delete_files = $POST[$field_name];
                            $get_name = $field_name.'_file_list';
                            $file_list = $this->$get_name;
                            foreach($file_list as $file) {
                                $found = false;
                                foreach($delete_files as $delete_file) {
                                    if ($file['path'] == $delete_file) {
                                        $found = true;
                                    }
                                }
                                if (!$found) {
                                    if (!Utils::endsWith($files_str, ";") && $files_str != "") {
                                        $files_str .= ";";
                                    }
                                    $files_str .= "[" . $file['file'] . "]" . $file['path'];
                                } else {
                                    if (file_exists(PATH.$file['path'])) {
                                        $this->data['deleted_files'][] = PATH.$file['path'];
                                    }
                                }
                            }
                        }
                        $this->$field_name = $files_str;
                    }
                } elseif (isset($FILES[$field_name]) && $this->fields[$field_name]['type'] == FileField::_cn) {
                    $file_field = $FILES[$field_name];
                    if (is_array($file_field['name'])) {
                        $files = array();
                        $i = 0;
                        while ($i < sizeof($file_field['name'])) {
                            if ($file_field['tmp_name'][$i] != "" && $file_field['error'][$i] == 0) {
                                $files[] = array("name" => $file_field['name'][$i], "tmp_name" => $file_field['tmp_name'][$i], "type" => $file_field['type'][$i], "error" => $file_field['error'][$i], "size" => $file_field['size'][$i]);
                            }
                            $i++;
                        }
                    } else {
                        $files = array(array("name" => $file_field['name'], "tmp_name" => $file_field['tmp_name'], "type" => $file_field['type'], "error" => $file_field['error'], "size" => $file_field['size']));
                    }
                    $result = $this->$field_name;
                    foreach($files as $file) {
                        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                        $basename = pathinfo($file['name'], PATHINFO_BASENAME);
                        $i = 0;
                        $uploadfile = UPLOADDIR.md5(basename($file['name'])+$i).".".$ext;
                        while (file_exists(PATH.$uploadfile)) {
                            $i++;
                            $uploadfile = UPLOADDIR.md5(basename($file['name'])+$i).".".$ext;
                        }
                        if (isset($this->fields[$field_name]['allowed_file_types'])) {
                            preg_split('/;/', $this->fields[$field_name]['allowed_file_types']);

                            if (mime_content_type($file['tmp_name']) != image/gif) {

                            }
                        }
                        if (move_uploaded_file($file['tmp_name'], PATH.$uploadfile)) {
                            if (!Utils::endsWith($result, ";") && $result != "") {
                                $result .= ";";
                            }
                            $result .= '['.$basename.']'.$uploadfile;
                        }
                        if (file_exists($file['tmp_name'])) {
                            unlink($file['tmp_name']);
                        }
                    }
                    $this->$field_name = $result;
                } elseif (!isset($FILES[$field_name]) && $this->fields[$field_name]['type'] == FileField::_cn) {
                    $this->$field_name = "";
                } else {
//                    $this->$field_name = null;
                }
            }
        }
        if (!empty($this->errors)) {
            throw new ValidationError($this->errors);
        }
        return $this;
    }

    public static function _cn() {
        return get_called_class();
    }

    public function get_form_fields() {
        $Form = new BaseModelForm(
            $model=get_class($this),
            $fields=$this->form_fields,
            $object_id=$this->id);
        return $Form->get_form_fields();
    }

    public function get_form() {
        $Form = new BaseModelForm(
            $model=get_class($this),
            $fields=$this->form_fields,
            $object_id=$this->id);
        return $Form->render();
    }

    public function get_field_verbose_name($field_name) {
        if (isset($this->fields[$field_name]['verbose_name'])) {
            return Translation::translate($this->fields[$field_name]['verbose_name']);
        } else {
            return Translation::translate(ucfirst($field_name));
        }
    }
}
