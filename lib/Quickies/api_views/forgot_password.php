<?php
    namespace Iekadou\Quickies;
    require_once("../../../../../../inc/include.php");

    try {
        $errors = array();
        $identification = (isset($_POST['identification']) ? htmlspecialchars($_POST['identification']) : false);
        $login_user = false;
        $User = new $UserClass();

        if ($identification == false || $identification == '') {
            $errors[] = "identification";
        } else {
            $login_user = $User->get_by(array(array('username', '=', $identification)));
            if ($login_user == false) {
                $login_user = $User->get_by(array(array('email', '=', $identification)));
            }
            if ($login_user == false) {
               $errors[] = "identification";
            }
        }
        if (!empty($errors)) {
            throw new ValidationError($errors);
        } else {
            $login_user->send_new_password();
            echo '{"url": "'.UrlsPy::get_url('home').'"}'; die();
        }
    } catch (ValidationError $e) { echo $e->stringify(); die(); }
