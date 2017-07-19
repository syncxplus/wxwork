<?php

namespace app;

use app\common\Service;

class Logout
{
    use Service;

    function get($f3)
    {
        $f3->clear('SESSION.AUTHENTICATION');
        $f3->clear('SESSION.AUTHORIZATION');
        header('location:' . $this->url($f3->get('BASE')));
    }
}
