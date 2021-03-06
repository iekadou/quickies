<?php
    namespace Iekadou\Quickies;
    require_once(getenv('INCLUDE_PHP_PATH'));

    class UserSerializer extends ApiSerializer {

        public function __construct()
        {
            $this->model = _i(USERCLASS);
            $this->fields = array('username', 'email', 'password');
            $this->filter_opts = array(array('id', '=', Account::get_user_id()));
            $this->allowed_methods = array('PUT');
            $this->write_only_fields = array('password');
            $this->serializer_instance_id = Account::get_user_id();
        }
    }

    $ApiView = new ApiView(
        $serializer = new UserSerializer()
    );
    $result = $ApiView->render();
    echo $result;