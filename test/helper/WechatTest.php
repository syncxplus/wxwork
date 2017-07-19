<?php

namespace test\helper;

use helper\Wechat;
use PHPUnit\Framework\TestCase;

class WechatTest extends TestCase
{
    public function testInit()
    {
        $f3 = \Base::instance();
        $wechat = new Wechat($f3);
        $this->assertNotEmpty($wechat);
        return $wechat;
    }

    /**
     * @depends testInit
     */
    public function testAccessToken($wechat)
    {
        $accessToken = $wechat->getAccessToken();
        $this->assertNotEmpty($accessToken);
        echo PHP_EOL, $accessToken, PHP_EOL;
        return $accessToken;
    }

    /**
     * @depends testInit
     */
    public function testJsTicket($wechat)
    {
        $jsTicket = $wechat->getJsTicket();
        $this->assertNotEmpty($jsTicket);
        echo PHP_EOL, $jsTicket, PHP_EOL;
        return $jsTicket;
    }

    /**
     * @depends testInit
     */
    public function testJsConfig($wechat)
    {
        $jsConfig = $wechat->getJsConfig('http://baidu.com');
        $this->assertTrue(is_array($jsConfig));
        print_r($jsConfig);
        return $jsConfig;
    }
}
