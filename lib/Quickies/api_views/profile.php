<?php
    namespace Iekadou\Quickies;
    require_once("../../../../../../inc/include.php");

    class UserSerializer extends ApiSerializer {

        public function __construct()
        {
            global $UserClass;
            $this->model = _i($UserClass);
            $this->fields = array('username', 'email', 'password', 'apnkey');
            $this->filter_opts = array(array('id', '=', Account::get_user_id()));
            $this->allowed_methods = array('PUT');
            $this->write_only_fields = array('password');
            $this->serializer_instance_id = Account::get_user_id();
        }
    }

    $ApiView = new ApiView(
        $serializer = _i(UserSerializer::_cn)
    );
    $result = $ApiView->render();
    echo $result;