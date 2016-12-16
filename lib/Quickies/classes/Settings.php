<?php
namespace Iekadou\Quickies;

class Settings extends BaseModel
{
    const _cn = "Iekadou\\Quickies\\Settings";

    protected $table = 'settings';
    protected $fields = array(
        'latest_worker_timestamp' => array('type' => TimestampField::_cn)
    );

}
