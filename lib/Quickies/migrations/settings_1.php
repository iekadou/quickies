<?php
$migration = array();
$migration['id']  =  "settings_1";
$migration['query'] = "CREATE TABLE `checkpoints`.`settings` (`id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, `latest_worker_timestamp` TIMESTAMP NOT NULL);";
$migration['fields'] = array (
  'latest_worker_timestamp' => 
  array (
    'type' => 'Iekadou\\Quickies\\TimestampField',
  ),
);