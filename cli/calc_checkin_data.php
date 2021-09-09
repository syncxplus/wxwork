<?php

use app\Checkin;
use helper\Wxwork;

require_once __DIR__ . '/index.php';

$start = $argv[1] ?? date('Y-m-d', strtotime('-7 days'));
$end = $argv[1] ?? date('Y-m-d');
echo "$start, $end\n";

$f3 = Base::instance();
$wxwork = new Wxwork($f3);

$users = $wxwork->getUserList();
$userid = array_column($users, 'userid');
array_multisort($userid, $users);

$checkin = new Checkin();
foreach ($userid as $key => $user) {
    $exception = false;
    $query = $checkin->getCheckinData($start, $end, [$user]);
    foreach ($query as $line) {
        if ($line->exception_type) {
            if (!$exception) {
                $exception = true;
                echo sprintf("%s:\n", $users[$key]->name);
            }
            echo sprintf("%s %s\n", date('Y-m-d H:m:s', $line->checkin_time), $line->exception_type);
        }
    }
}