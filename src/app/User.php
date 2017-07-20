<?php

namespace app;

use app\common\Service;
use helper\Wxwork;

class User
{
    use Service;

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
}
