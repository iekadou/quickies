<?php
namespace Iekadou\Quickies;

abstract class BaseModel
{
    protected $data = array();
    protected $db_connection = null;
    protected $id = null;
    protected $fields = array();
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
            $this->data[$field_name] = "";
        }
    }

    public function __isset($field_name) {
        return isset($this->id) || isset($this->data[$field_name]) || isset($this->data[$field_name.'_id']) || method_exists($this, 'get_'.$field_name) ||
            ((strpos($field_name, '_display') > 0) && isset($this->data[substr($field_name, 0, strpos($field_name, '_display'))]));
    }

    public function __get($field_name) {
        if ($field_name == 'id') {
            return $this->id;
        }
        if ($field_name == in_array($field_name, array_keys($this->fields)) || method_exists($this, 'get_'.$field_name)) {
            $method_name = 'get_' . $field_name;
            if (method_exists($this, $method_name)) {
                return $this->$method_name();
            }
            $field = $this->fields[$field_name]['type'];
            $field = new $field($this->fields[$field_name]);
            return $field->_get($this, $field_name);
        } else if ($field_name == in_array($field_name.'_id', array_keys($this->fields))) {
            $field_name .= '_id';
            $method_name = 'get_' . $field_name;
            if (method_exists($this, $method_name)) {
                return $this->$method_name();
            }
            $field = $this->fields[$field_name]['type'];
            $field = new $field($this->fields[$field_name]);
            $id = $field->_get($this, $field_name);
            $obj = $this->fields[$field_name]['foreign_type'];
            $obj = new $obj();
            $obj = $obj->get($id);
            return $obj;
        } else if (strpos($field_name, '_display') > 0) {
            $field_name = substr($field_name, 0, strpos($field_name, '_display'));
            $choices = $this->fields[$field_name]['choices'];
            return $choices::get_by_id($this->$field_name);
        } else {
            return $this->$field_name;
        }
    }

    public function __set($field_name, $value) {
        if ($field_name == in_array($field_name, array_keys($this->fields)) || $field_name.'_id' == in_array($field_name, array_keys($this->fields))) {
            $method_name = 'set_'.$field_name;
            if (method_exists($this, $method_name)) {
                return $this->$method_name($value);
            }
            $field = $this->fields[$field_name]['type'];
            $field = new $field($this->fields[$field_name]);
            $field->_set($this, $field_name, $value);
            return $this;
        } else {
            $this->$field_name = $value;
            return $this;
        }
    }

    public function makemigrations() {
        $new_fields = array();
        $del_fields = array();
        $change_fields = array();
        $dir = new \DirectoryIterator(PATH.'migrations/');
        $latest_migration = 0;
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()) {
                $filename = $fileinfo->getFilename();
                $regex = "/^".$this->table."_[0-9].php$/";
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
                if (!isset($migration['fields'][$field_name])) {
                    $new_fields[$field_name] = $this->fields[$field_name];
                } else {
                    $changes = $this->_detect_field_changes($this->fields[$field_name], $migration['fields'][$field_name]);
                    if (!empty($changes)) {
                        $change_fields[$field_name] = $changes;
                    }
                }
            }
            foreach($migration['fields'] as $field_name => $field) {
                if (!isset($this->fields[$field_name])) {
                    $del_fields[$field_name] = $migration['fields'][$field_name];
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
                array_push($query_parts, ", ".$this->_get_field_query($field_name, $field));
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
            echo "Nothing to migrate at: ".$this->table.'<br>';
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
            echo "Migrations created at: ".$this->table.'<br>';
        }
    }

    protected function _get_field_query($field_name, $field) {
        switch($field['type']) {
            case "Iekadou\\Quickies\\BooleanField":
                return "`".$field_name."` tinyint(1) NOT NULL";
                break;
            case "Iekadou\\Quickies\\ForeignKeyField":
                return "`".$field_name."` int(11) NOT NULL";
                break;
            case "Iekadou\\Quickies\\IntegerChoiceField":
                return "`".$field_name."` int(5) NOT NULL";
                break;
            case "Iekadou\\Quickies\\IntegerField":
                return "`".$field_name."` int(15) NOT NULL";
                break;
            case "Iekadou\\Quickies\\PasswordField":
                $max_length = (isset($field['max_length'])) ? $field['max_length'] : 254;
                return "`".$field_name."` varchar(".$max_length.") NOT NULL";
                break;
            case "Iekadou\\Quickies\\TextField":
                return "`".$field_name."` text NOT NULL";
                break;
            case "Iekadou\\Quickies\\TimestampField":
                return "`".$field_name."` TIMESTAMP() NOT NULL";
                break;
            case "Iekadou\\Quickies\\UrlField":
                $max_length = (isset($field['max_length'])) ? $field['max_length'] : 254;
                return "`".$field_name."` varchar(".$max_length.") NOT NULL";
                break;
            case "Iekadou\\Quickies\\VarcharField":
                $max_length = (isset($field['max_length'])) ? $field['max_length'] : 254;
                return "`".$field_name."` varchar(".$max_length.") NOT NULL";
                break;
            case "Iekadou\\Quickies\\DecimalField":
                $pre_dot_precision = (isset($field['pre_dot_precision'])) ? $field['pre_dot_precision'] : 10;
                $pos_dot_precision = (isset($field['pos_dot_precision'])) ? $field['post_dot_precision'] : 5;
                return "`".$field_name."` DECIMAL(".$pre_dot_precision.", ".$pos_dot_precision.") NOT NULL";
                break;
            default:
                echo "UNKNOWN TYPE: ".$field['type']." at field: ".$field_name;
                throw new ValidationError("UNKNOWN TYPE: ".$field['type']." at field: ".$field_name);

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
                        ($field1['type'] == "Iekadou\\Quickies\\VarcharField" && $key == 'max_length') ||
                        ($field1['type'] == "Iekadou\\Quickies\\ForeignKeyField" && $key == 'foreign_type') ||
                        ($field1['type'] == "Iekadou\\Quickies\\IntegerChoiceField" && $key == 'choices')) {
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
        $id = $this->db_connection->real_escape_string(htmlentities($id, ENT_QUOTES));
        $obj_query = $this->db_connection->query("SELECT * FROM ".$this->table." WHERE id = '" . $id . "';");
        if ($obj_query->num_rows == 1) {
            $obj = $obj_query->fetch_object();
            foreach($this->fields as $field_name => $field) {
                $this->$field_name = $obj->$field_name;
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
            $field_wrapper = $this->fields[$field_name]['type'];
            $field_wrapper = new $field_wrapper();
            if (!$field_wrapper->_validate_pre_db($this, $field_name)) {
                $this->errors[] = $field_name;
            }
            if ($i > 0) {
                $update_str .= ", ";
            }
            $i++;
            $update_str .= $field_name." = '".$this->$field_name."'";
        }
        if (!empty($this->errors)) {
            throw new ValidationError($this->errors);
        }
        $obj_query = $this->db_connection->query("UPDATE ".$this->table." set ".$update_str." WHERE id = '" . $this->id . "';");
        if ($obj_query) {
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
            $field_wrapper = $this->fields[$field_name]['type'];
            $field_wrapper = new $field_wrapper();
            if (!$field_wrapper->_validate_pre_db($this, $field_name)) {
                $this->errors[] = $field_name;
            }
            if ($i > 0) {
                $insert_str .= ", ";
            }
            $i++;
            $insert_str .= $field_name;
        }
        $insert_str .= ') VALUES (';
        $i = 0;
        foreach($this->fields as $field_name => $field) {
            if ($i > 0) {
                $insert_str .= ", ";
            }
            $i++;
            if (!isset($this->$field_name)) {
                $this->$field_name = '';
            }
            $insert_str .= "'".$this->$field_name."'";
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
            $id = $this->db_connection->real_escape_string(htmlentities($id, ENT_QUOTES));
        } else {
            $id = $this->id;
        }
        if ($id) {
            $user_query = $this->db_connection->query("DELETE FROM ".$this->table." WHERE id = '" . $id . "';");
            if ($user_query) {
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
            if ($i > 0) {
                $condition_str .= ' and ';
            } else {
                $condition_str = "WHERE ";
            }
            if ($condition[0] == 'id' || in_array($condition[0], array_keys($this->fields))) {
                $i++;
                $condition_str .= $condition[0]." ".$condition[1]." '".$this->db_connection->real_escape_string(htmlentities($condition[2], ENT_QUOTES))."'";
            }
        }
        if ($i > 0) {
            $obj_delete = $this->db_connection->query("DELETE FROM ".$this->table." ".$condition_str.";");
            return $obj_delete != false;
        } else {
            return false; // TODO hint: do not delete all by accident
        }

    }

    public function count_by($conditions, $sortings=array())
    {
        $this->reset_errors();
        $condition_str = '';
        $i = 0;
        foreach($conditions as $condition) {
            if ($i > 0) {
                $condition_str .= ' and ';
            } else {
                $condition_str = "WHERE ";
            }
            if ($condition[0] == 'id' || in_array($condition[0], array_keys($this->fields))) {
                $i++;
                $condition_str .= $condition[0]." ".$condition[1]." '".$this->db_connection->real_escape_string(htmlentities($condition[2], ENT_QUOTES))."'";
            }
        }
        $order_str = '';
        $i = 0;
        foreach($sortings as $sorting) {
            if ($i > 0) {
                $order_str .= ', ';
            } else {
                $order_str = 'ORDER BY ';
            }
            if ($sorting[0] == 'id' || in_array($sorting[0], array_keys($this->fields))) {
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
            if ($i > 0) {
                $condition_str .= ' and ';
            } else {
                $condition_str = "WHERE ";
            }
            if ($condition[0] == 'id' || in_array($condition[0], array_keys($this->fields))) {
                $i++;
                $condition_str .= $condition[0]." ".$condition[1]." '".$this->db_connection->real_escape_string(htmlentities($condition[2], ENT_QUOTES))."'";
            }
        }
        $order_str = '';
        $i = 0;
        foreach($sortings as $sorting) {
            if ($i > 0) {
                $order_str .= ', ';
            } else {
                $order_str = 'ORDER BY ';
            }
            if ($sorting[0] == 'id' || in_array($sorting[0], array_keys($this->fields))) {
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
                $this->$field_name = $obj->$field_name;
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
            if ($i > 0) {
                $condition_str .= ' and ';
            } else {
                $condition_str = "WHERE ";
            }
            if ($condition[0] == 'id' || in_array($condition[0], array_keys($this->fields))) {
                $i++;
                $condition_str .= $condition[0]." ".$condition[1]." '".$this->db_connection->real_escape_string(htmlentities($condition[2], ENT_QUOTES))."'";
            }
        }
        $order_str = '';
        $i = 0;
        foreach($sortings as $sorting) {
            if ($i > 0) {
                $order_str .= ', ';
            } else {
                $order_str = 'ORDER BY ';
            }
            if ($sorting[0] == 'id' || in_array($sorting[0], array_keys($this->fields))) {
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
                $class = get_class($this);
                $obj = new $class();
                foreach($this->fields as $field_name => $field) {
                    $obj->$field_name = $row->$field_name;
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
                    $this->$field_name = $POST[$field_name];
                } else {
                    $this->$field_name = null;
                }
            }
        }
        if (!empty($this->errors)) {
            throw new ValidationError($this->errors);
        }
        return $this;
    }

}
