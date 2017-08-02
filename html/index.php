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
