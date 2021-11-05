<?php

namespace app;

use app\common\AppBase;

class Menu extends AppBase
{
    function get($f3)
    {
        $f3->set('title', '菜单');
        echo \Template::instance()->render('menu.html');
    }
}
