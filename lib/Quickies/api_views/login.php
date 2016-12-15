<?php
    namespace Iekadou\Quickies;
    require_once("../../../../../../inc/include.php");

    try {
        $User = new $UserClass();
        $errors = array();
        $identification = (isset($_POST['identification']) ? htmlspecialchars($_POST['identification']) : false);
        $referrer = (isset($_POST['referrer']) ? htmlspecialchars($_POST['referrer']) : false);

        if ($identification == false || $identification == '') {
            $errors[] = "identification";
        } else {
            $logged_in_user = $User->get_by(array(array('username', '=', $identification)));
            if ($logged_in_user == false) {
                $logged_in_user = $User->get_by(array(array('email', '=', $identification)));
            }
            if ($logged_in_user == false) {
               $errors[] = "identification"; $errors[] = "password";
            }
        }
        $password = (isset($_POST['password']) ? htmlspecialchars($_POST['password']) : false);
        if ($password == false || $password == '') {
            $errors[] = "password";
        }
        if (!empty($errors)) {
            throw new ValidationError($errors);
        } else {
            Account::login($User, $password);

            if (isset($_POST['remember_me']) && $_POST['remember_me'] == 'true') {
                Account::generate_remember_token($User->id);
            }
            if ($referrer) {
                echo '{"url": "'.$referrer.'"}'; die();
            } else {
                echo '{"url": "'.UrlsPy::get_url('account').'"}'; die();
            }
        }
    } catch (ValidationError $e) { echo $e->stringify(); die(); }
