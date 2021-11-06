<?php

namespace app\common;

use helper\Wxwork;

class AppBase
{
    use AppHelper;

    protected $error = ['code' => -1, 'text' => ''];
    protected $userid;

    function beforeRoute($f3)
    {
        if ($this->userid = $f3->get('SESSION.USERID')) {
            $f3->set('userid', $this->userid);
            $f3->set('jsConfig', (new Wxwork($f3))->getJsConfig($f3->REALM));
        } else {
            if ($f3->VERB == 'GET') {
                setcookie('target', $f3->REALM, 0, '/');
            } else {
                setcookie('target', $this->url(), 0, '/');
            }
            $f3->reroute($this->url('/Login'));
        }
    }

    function jsonResponse($data = [])
    {
        $result = [
            'error' => $this->error,
            'data' => $data
        ];
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }
}
