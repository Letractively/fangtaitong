<?php
set_time_limit(0);
if (!flock(($_lock = fopen(basename(__FILE__) . '.lck', 'w')), LOCK_EX | LOCK_NB))
{
	exit(date('c') . ' another proccess is running' . PHP_EOL);
}

require_once 'env.php';

$process = new Process_DelUser;
$process();
