<?php

namespace app\common;

trait Service
{
    function url($target = '')
    {
        $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' || isset($headers['X-Forwarded-Proto']) && $headers['X-Forwarded-Proto'] == 'https' ? 'https' : 'http';
        $host = $_SERVER['SERVER_NAME'];
        $port = $_SERVER['SERVER_PORT'] == 80 ? '' : ':' . $_SERVER['SERVER_PORT'];
        $base = rtrim(strtr(dirname($_SERVER['SCRIPT_NAME']), '\\', '/'), '/');
        return $scheme . '://' . $host . $port . $base . $target;
    }

    function ip() {
        if(function_exists('getenv')) {
            if(getenv('Http_X_Forwarded_For')) {
                return getenv('Http_X_Forwarded_For');
            } else if(getenv('Http_X_Real_IP')) {
                return getenv('Http_X_Real_IP');
            }
        }
        return $_SERVER['REMOTE_ADDR'];
    }
}
