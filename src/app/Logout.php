<?php

namespace app;

use app\common\AppHelper;

class Logout
{
    use AppHelper;

    function get($f3)
    {
        $f3->clear('SESSION.USERID');
        header('location:' . $this->url());
    }
}
