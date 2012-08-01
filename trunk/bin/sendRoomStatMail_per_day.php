<?php
require_once 'env.php';

$idx = Process::input('-i');
$all = Process::input('-a');

if (!flock(($_lock = fopen(basename(__FILE__) . ".{$idx}.lck", 'w')), LOCK_EX | LOCK_NB))
{
	exit(date('c') . ' another proccess is running' . PHP_EOL);
}

set_time_limit(0);
$process = new Process_SendRoomStatMail($idx, $all);
$process();
// End of file : sendRoomStatMail_per_day.php
