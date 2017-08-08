<?php
namespace Iekadou\Quickies;
require_once("../../../../../../inc/include.php");

class UserSerializer extends ApiSerializer {

    public function __construct()
    {
        $this->model = _i(USERCLASS);
        $this->fields = array('username', 'email', 'password');
        $this->allowed_methods = array('POST');
    }
}

$ApiView = new ApiView(
    $serializer = new UserSerializer()
);
$result = $ApiView->render();
echo $result;
