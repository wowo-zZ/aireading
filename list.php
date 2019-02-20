<?php
/**
 *
 * User: wowo
 * Date: 2019/2/19 下午2:55
 */
include('config.php');
include('db.php');

$db = new DB($config['db_config']);
$pictures = $db->select('select * from picture');

include('./list.html');
