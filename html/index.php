<?php

define('HTML', __DIR__);
define('ROOT', dirname(HTML));

require_once ROOT . '/vendor/autoload.php';

call_user_func(function ($f3) {
    $f3->config(implode(',', [
        ROOT . '/cfg/system.ini',
        ROOT . '/cfg/map.ini',
        ROOT . '/cfg/route.ini',
        ROOT . '/cfg/local.ini',
    ]));

    $sysdir = [
        'logs' => ROOT . '/runtime/logs/',
        'downloads' => HTML . '/data/downloads/',
        'uploads' => HTML . '/data/uploads/',
    ];

    $f3->mset([
        'AUTOLOAD' => ROOT . '/src/',
        'LOGS' => $sysdir['logs'],
        'UI' => ROOT . '/tpl/',
        'UPLOADS' => $sysdir['uploads'],
        'ONERROR' => function ($f3) {
            $error = $f3->get('ERROR');
            if (!$f3->get('DEBUG')) {
                unset($error['trace']);
            }
            if ($f3->get('AJAX')) {
                echo json_encode(['error' => $error], JSON_UNESCAPED_UNICODE);
            } else {
                $f3->set('error', $error);
                echo Template::instance()->render('error.html');
            }
        }
    ]);

    foreach ($sysdir as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    if (PHP_SAPI != 'cli') {
        $f3->run();
    }
}, Base::instance());

class Logger {
    private $file;

    function __construct()
    {
        if (!is_dir($dir = Base::instance()->LOGS)) {
            mkdir($dir,Base::MODE,true);
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
                $message = @sprintf($message, ... array_pad($data, $expect, ''));
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
        file_put_contents($this->file, date('[Y-m-d H:i:s] [') . $level . '] [' . Base::instance()->ip() . '] ' . $message . PHP_EOL,LOCK_EX | FILE_APPEND);
    }
}