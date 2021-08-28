<?php

namespace test\helper;

use helper\Wxwork;
use PHPUnit\Framework\TestCase;

class WechatUser extends TestCase
{
    public function testUsers()
    {
        $wechat = new Wxwork(\Base::instance());
        $users = $wechat->getUserList();
        $this->assertNotEmpty($users);
        foreach ($users as $user) {
            echo json_encode($user, JSON_UNESCAPED_UNICODE), PHP_EOL;
        }
    }
}
