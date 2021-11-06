<?php

namespace app;

use app\common\AppBase;
use helper\Wxwork;

class Menu extends AppBase
{
    function get($f3)
    {
        $f3->set('title', '菜单');
        $leaders = $f3->get('leaders');
        if (empty($leaders)) {
            $users = (new Wxwork($f3))->getUserList();
            foreach ($users as $user) {
                if ($user->is_leader_in_dept) {
                    $leaders[] = $user->UserId;
                }
            }
            $leaders = array_unique($leaders);
            sort($leaders);
            $f3->set('leaders', implode(',', $leaders), 500);
        } else {
            $leaders = explode(',', $leaders);
        }
        if (in_array($this->userid, $leaders)) {
            $f3->set('leader', true);
        }
        echo \Template::instance()->render('menu.html');
    }
}
