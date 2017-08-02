<?php

namespace app\common;

trait AppHelper
{
    function url($target = '')
    {
        $scheme = strtolower(substr($_SERVER["SERVER_PROTOCOL"], 0, strpos($_SERVER["SERVER_PROTOCOL"], '/')));
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
