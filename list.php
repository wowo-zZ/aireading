<?php
/**
 *
 * User: wowo
 * Date: 2019/2/19 下午2:55
 */
include('config.php');
include('db.php');
include('db_sqlite.php');
$db_sqlite = new DB_Sqlite('./sqlite.db');
$pictures = $db_sqlite->select('select * from picture');
include('./list.html');
