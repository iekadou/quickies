<?php
namespace Iekadou\Quickies;

class Settings extends BaseModel
{
    protected $table = 'settings';
    protected $fields = array(
        'latest_worker_timestamp' => array('type' => "Iekadou\\Quickies\\TimestampField")
    );

}
