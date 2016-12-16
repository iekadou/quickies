<?php
namespace Iekadou\Quickies;
require_once("../../../../../../inc/include.php");

class UserSerializer extends ApiSerializer {

    public function __construct()
    {
        global $UserClass;
        $this->model = _i($UserClass);
        $this->fields = array('username', 'email', 'activated', 'admin', 'apnkey');
        if (!Account::get_user()->admin) {
            $this->filter_opts = array(array('username', '=', Account::get_user()->username));
        }
    }
}

$ApiView = new ApiView(
    $serializer = _i(UserSerializer::_cn)
);
$result = $ApiView->render();
echo $result;
