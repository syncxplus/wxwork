<?php

namespace test\helper;

use app\Checkin;
use PHPUnit\Framework\TestCase;

class WechatCheckin extends TestCase
{
    public function testQuery()
    {
        $checkin = new Checkin();
        print_r($checkin->getCheckinData());
    }
}
