<?php
    namespace Iekadou\Quickies;
    require_once(getenv('INCLUDE_PHP_PATH'));

    try {
        if (Account::is_logged_in() != true) {
            Utils::raise404();
            die();
        }
        Account::get_user()->send_activation_email();
        echo '{"url": "'.UrlsPy::get_url('account').'"}'; die();
    } catch (ValidationError $e) { echo $e->stringify(); die(); }
