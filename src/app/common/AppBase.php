<?php

namespace app\common;

use helper\Wechat;

class AppBase
{
    use Service;

    protected $error = ['code' => -1, 'text' => ''];
    protected $user;

    function beforeRoute($f3)
    {
        if (!$f3->get('SESSION.AUTHENTICATION')) {
            if ($f3->VERB == 'GET') {
                setcookie('target', $f3->REALM, 0, '/');
            } else {
                setcookie('target', $this->url(), 0, '/');
            }
            $f3->reroute($this->url('/Login'));
        }
        $this->user = [
            'name' => $f3->get('SESSION.AUTHENTICATION'),
            'role' => $f3->get('SESSION.AUTHORIZATION')
        ];
        $f3->set('user', $this->user);
        $f3->set('jsConfig', (new Wechat($f3))->getJsConfig($f3->REALM));
    }

    function jsonResponse($data = [])
    {
        $result = ['error' => $this->error];
        if (is_array($data)) {
            $result = array_merge($result, $data);
        } else {
            $result[] = $data;
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }
}
