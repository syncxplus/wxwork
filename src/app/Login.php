<?php

namespace app;

use app\common\Service;
use helper\Logger;

class Login
{
    use Service;

    private $administrator = ['debug'];

    function get($f3)
    {
        $f3->set('title', '登录');
        echo \Template::instance()->render('login.html');
    }

    function post($f3)
    {
        $logger = new Logger($f3->LOGS);
        $username = $_POST['username'];
        $password = $_POST['password'];

        $logger->debug('Receive login request', $username);

        if ($this->validate($username, $password)) {
            $logger->info('User (%s) login success', $username);
            $f3->set('SESSION.AUTHENTICATION', $username);
            $f3->set('SESSION.AUTHORIZATION', in_array($username, $this->administrator) ? 'administrator' : 'user');
            echo json_encode([
                'error' => ['code' => 0]
            ]);
        } else {
            $logger->warn('User (%s) login failure', $username);
            echo json_encode([
                'error' => ['code' => 0, 'text' => 'login error']
            ]);
        }
    }

    function validate($username, $password)
    {
        return true;
    }
}
