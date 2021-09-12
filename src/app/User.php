<?php

namespace app;

use app\common\AppHelper;
use helper\Wxwork;

class User
{
    use AppHelper;

    function auth($f3)
    {
        $wxwork = new Wxwork($f3);
        header('location:' . $wxwork->getOauth2Url($this->url('/auth/callback')));
    }

    function callback($f3)
    {
        $code = $_GET['code'];
        $state = $_GET['state'];

        $logger = new \Logger();
        $logger->info("code: $code, state: $state");

        $wxwork = new Wxwork($f3);
        $userinfo = $wxwork->getUserInfo($code, true);
        $users = $userinfo->UserId == 'jibo' ? $wxwork->getUserList() : [$wxwork->getUser($userinfo->UserId)];

        $checkin = new Checkin();
        $checkin->render(date('Y-m-d', strtotime('-30 days')), date('Y-m-d'), $users);
    }

    function all($f3)
    {
        $wx = new Wxwork($f3);
        $users = json_encode($wx->getUserList(), JSON_UNESCAPED_UNICODE);
        if (json_last_error()) {
            echo json_last_error_msg();
        } else {
            echo $users;
        }
    }
}
