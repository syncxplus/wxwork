<?php

namespace helper;

use app\common\Service;

class Logger
{
    use Service;

    private $file;

    function __construct($dir = '/tmp')
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $this->file = $dir . date('/Y-m-d.') . 'log';
    }

    function debug($message, $args = false)
    {
        $this->write($message, $args, 'DBG');
    }

    function info($message, $args = false)
    {
        $this->write($message, $args, 'INF');
    }

    function warn($message, $args = false)
    {
        $this->write($message, $args, 'WRN');
    }

    function error($message, $args = false)
    {
        $this->write($message, $args, 'ERR');
    }

    private function write($message, $args, $level = 'DBG')
    {
        if ($args) {
            $expect = substr_count($message, '%');
            if ($expect) {
                if (is_array($args)) {
                    $data = $args;
                } else if (is_scalar($args) || (is_object($args) && method_exists($args, "__toString"))) {
                    $data = [$args];
                } else if (is_object($args)) {
                    $data = ['[object ' . get_class($args) . ']'];
                } else {
                    $data = ['[' . gettype($args) . ']'];
                }
                $message = sprintf($message, ... array_pad($data, $expect, ''));
            } else {
                if (is_array($args)) {
                    $data = implode(', ', $args);
                } else if (is_scalar($args) || (is_object($args) && method_exists($args, "__toString"))) {
                    $data = $args;
                } else if (is_object($args)) {
                    $data = '[object ' . get_class($args) . ']';
                } else {
                    $data = '[' . gettype($args) . ']';
                }
                $message .= ' ... ' . $data;
            }
        }
        file_put_contents($this->file, date('[Y-m-d H:i:s] [') . $level . '] [' . $this->ip() . '] ' . $message . PHP_EOL,LOCK_EX | FILE_APPEND);
    }
}
