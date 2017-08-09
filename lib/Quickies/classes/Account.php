<?php
namespace Iekadou\Quickies;

class Account
{
    const _cn = "Iekadou\\Quickies\\Account";

    private static $db_connection = null;
    private static $user = null;
    private static $user_is_logged_in = false;
    private static $errors = array();

    public function __construct()
    {
        Account::$db_connection = _i(DBConnector::_cn);
        if (Account::$db_connection->get_connect_errno()) {
            Account::$errors[] = "db";
            throw new ValidationError(Account::$errors);
        }
        if (isset($_COOKIE['remember_me'])) {
            Account::remember_user($_COOKIE['remember_me']);
        }
        if (isset(getallheaders()['APNKEY'])) {
            Account::api_user(getallheaders()['APNKEY']);
        }
    }

    public static function login($user_obj, $password)
    {
        if (isset($password)) {
            if (password_verify($password, $user_obj->password)) {
                $_SESSION['user_logged_in'] = true;
                $_SESSION['user_id'] = $user_obj->id;
                Account::$user_is_logged_in = true;
                return true;
            } else {
                Account::$errors[] = "password";
                Account::$errors[] = "identification";
                Account::$errors[] = "username";
                throw new ValidationError(Account::$errors);
            }
        } else {
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user_obj->id;
            Account::$user_is_logged_in = true;
            return true;
        }
    }

    public static function logout()
    {
        $_SESSION = array();
        session_destroy();
        setcookie("remember_me", "", time()-3600, '/');
        Account::$user_is_logged_in = false;
        return true;
    }

    public static function is_logged_in()
    {
        if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] == true) {
           return true;
        }
        return false;
    }

    public static function get_user_id()
    {
        if (isset($_SESSION['user_id'])) {
            return $_SESSION['user_id'];
        }
        return 0;
    }

    public static function get_user()
    {
        if (isset(Account::$user)) {
            return Account::$user;
        } else {
            $User = _i(USERCLASS);
            Account::$user = $User->get(Account::get_user_id());
            return Account::$user;
        }
    }

    public static function generate_remember_token($userid) {
        $remember_token = str_shuffle(MD5(microtime()));
        setcookie("remember_me", $remember_token, time() + 3600*24*365, '/');
        _i(DBConnector::_cn)->query("DELETE FROM remember where `userid` = '".$userid."';");
        _i(DBConnector::_cn)->query("INSERT INTO remember (userid, token, expires) VALUES ('".$userid."', '".hash('sha256', $remember_token)."', '".(time()+3600*24*365)."');");
        return $remember_token;
    }

    public static function remember_user($remember_token) {
        $obj_query = _i(DBConnector::_cn)->query("SELECT userid FROM remember WHERE token = '" . hash('sha256', $remember_token) . "' and expires > ".time().";");
        if ($obj_query->num_rows == 1) {
            $obj = $obj_query->fetch_object();
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $obj->userid;
            Account::$user_is_logged_in = true;
        }
    }

    public static function api_user($apnkey) {
        $obj_query = _i(DBConnector::_cn)->query("SELECT id FROM user WHERE apnkey = '".Account::$db_connection->real_escape_string($apnkey)."';");
        if ($obj_query->num_rows == 1) {
            $obj = $obj_query->fetch_object();
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $obj->id;
            Account::$user_is_logged_in = true;
        }
    }
}
