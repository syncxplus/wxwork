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
        $wxwork = new Wxwork($f3);
        var_dump($code);
        var_dump($state);
        echo json_encode($wxwork->getUserInfo($code, true));
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
