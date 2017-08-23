<?php
namespace Iekadou\Quickies;
require_once(getenv('INCLUDE_PHP_PATH'));

class UserSerializer extends ApiSerializer {

    public function __construct()
    {
        $this->model = _i(USERCLASS);
        $this->fields = _i(USERCLASS)->form_fields;
        $this->filter_opts = array(array('id', '=', Account::get_user_id()));
        $this->allowed_methods = array('PUT');
    }
}

$ApiView = new ApiView(
    $serializer = new UserSerializer()
);
$result = $ApiView->render();
echo $result;