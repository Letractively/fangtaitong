<?php
set_time_limit(0);
require_once 'env.php';
require_once 'PHPMailer/class.phpmailer.php';

$mailer = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('mailer');

$ary = array();
foreach (explode(',', $mailer['list']) as $val)
{
    $ary[] = array_merge($mailer['base'], array('user' => sprintf($mailer['base']['user'], $val)));
}

if (empty($ary))
{
    exit('Err: No mailer!');
}

$ids = array();
for ($idx = 0, $len = sizeof($ary); $idx < $len; $idx++)
{
    if (!@flock(($flock = fopen(basename(__FILE__) . ".{$idx}.lck", 'w')), LOCK_EX | LOCK_NB))
    {
        @fclose($flock);
        continue;
    }

    switch ($pid = pcntl_fork())
    {
    case -1:
        echo "Fork[{$idx}] Failed!\n";
        @fclose($flock);
        break;

    case 0:
        $process = new Process_MailJob($ary[$idx], $idx, $len);
        $process();
        exit;

    default :
        $ids[$pid] = $flock;
        break;
    }
}

while ($ids)
{
    $pid = pcntl_wait($sta);
    if (isset($ids[$pid]))
    {
        @fclose($ids[$pid]);
        unset($ids[$pid]);
    }
}
exit;
