<?php
namespace Iekadou\Quickies;
require_once("../../../../../../inc/include.php");

class UserSerializer extends ApiSerializer {

    public function __construct()
    {
        global $UserClass;
        $this->model = _i($UserClass);
        $this->fields = array('username', 'email', 'apnkey', 'activated', 'admin');
        $this->filter_opts = array(array('id', '=', Account::get_user_id()));
        $this->allowed_methods = array('PUT');
    }
}

$ApiView = new ApiView(
    $serializer = new UserSerializer()
);
$result = $ApiView->render();
echo $result;