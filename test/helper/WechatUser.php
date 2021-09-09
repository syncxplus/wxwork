<?php

namespace test\helper;

use helper\Wxwork;
use PHPUnit\Framework\TestCase;

class WechatUser extends TestCase
{
    /*
    {
    "userid":"",
    "name":"",
    "department":[7,1],
    "position":"",
    "mobile":"",
    "gender":"1",
    "email":"",
    "avatar":"",
    "status":1,
    "enable":1,
    "isleader":1,
    "extattr":{
        "attrs":[{
            "name":"入职日期","value":"","type":0,"text":{"val""}}
        ]},
    "hide_mobile":0,
    "english_name":"",
    "telephone":"",
    "order":[0,0],
    "external_profile":{"external_attr":[],"external_corp_name":""},
    "main_department":1,
    "qr_code":"",
    "alias":"",
    "is_leader_in_dept":[1,1],
    "address":"",
    "thumb_avatar":""}
    */
    public function testUsers()
    {
        $avg = (53338 / 12 + 74520 / 12) / 2;
        $rate = 0.1 / 12;
        echo sprintf("\nAvg value: %6.2f\n", $avg);
        $wechat = new Wxwork(\Base::instance());
        $users = $wechat->getUserList();
        $this->assertNotEmpty($users);
        $sort = array_column($users, 'userid');
        array_multisort($sort, $users);
        foreach ($users as $user) {
            //echo json_encode($user, JSON_UNESCAPED_UNICODE), PHP_EOL;
            echo sprintf("%8s: %s", $user->name, $user->status);
            $name = '入职日期';
            $attrs = array_column($user->extattr->attrs, 'value', 'name');
            if ($attrs[$name]) {
                $n = date('n', time());
                $m = ($n > 6) ? 7 : 1;
                $date = date('Y') . '-0' . $m . '-01';
                $p1 = round((strtotime($date) - strtotime($attrs[$name])) / 3600 / 24 / 30);
                $p2 = array_sum($user->is_leader_in_dept) * 6;
                $output = $avg * pow(1 + $rate, $p1 + $p2);
                echo sprintf('%10.2f (%s, %d, %d) ', $output, $attrs[$name], $p1, $p2);
            }
            echo PHP_EOL;
        }
    }
}
