<?php

define('HTML', __DIR__);
define('ROOT', dirname(HTML));

require_once ROOT . '/vendor/autoload.php';

call_user_func(function ($f3) {

    if (!$f3->log) {
        $f3->config(ROOT . '/cfg/system.ini');
        $f3->config(ROOT . '/cfg/local.ini');
        $f3->mset([
            'AUTOLOAD' => ROOT . '/src/',
            'LOGS' => ROOT . '/data/logs/'
        ]);

        if (PHP_SAPI != 'cli') {
            $f3->config(ROOT . '/cfg/map.ini');
            $f3->config(ROOT . '/cfg/route.ini');
            $f3->mset([
                'UI' => ROOT . '/tpl/',
                'UPLOADS' => ROOT . '/data/uploads/',
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
            $f3->run();
        }
    }
}, Base::instance());
