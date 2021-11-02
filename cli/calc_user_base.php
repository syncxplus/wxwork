<?php

use helper\Wxwork;
use PhpOffice\PhpSpreadsheet\IOFactory;

require_once __DIR__ . '/index.php';

/* 文件格式: name,base */
$excel = IOFactory::load(ROOT . '/runtime/base.xlsx');
$data = array_column($excel->getSheet(0)->toArray(), 1, 0);

$wechat = new Wxwork(\Base::instance());
$users = array_column(array_map(function ($user){
    $attrs = array_column($user->extattr->attrs, 'value', 'name');
    return [
        $user->name,
        [
            'status' => $user->status,
            'onboard' => $attrs['入职日期'],
            'manager' =>  array_sum($user->is_leader_in_dept) > 0,
        ],
    ];
}, $wechat->getUserList()), 1, 0);

$avg = (53338 / 12 + 74520 / 12) / 2;
$rate = 0.1 / 12;
$calc = [];
foreach ($data as $name => $base) {
    if ($attrs = $users[$name]) {
        $n = date('n', time());
        $m = ($n > 6) ? 7 : 1;
        $date = date('Y') . '-0' . $m . '-01';
        $p = round((strtotime($date) - strtotime($attrs['onboard'])) / 3600 / 24 / 30);
        $output = $avg * pow(1 + $rate, $p);
        $calc[$name] = [
            'base' => $base,
            'plus' => $output - $base,
            'manager' => 2500 * $attrs['manager'],
            'onboard' => $attrs['onboard'],
        ];
    } else {
        $calc[$name] = '404';
    }
}

uasort($calc, function ($a, $b) {
    $x = $a['manager'] ?? -1;
    $y = $b['manager'] ?? -1;
    return $y - $x;
});

foreach ($calc as $name => $line) {
    if (is_array($line)) {
        echo $name;
        foreach ($line as $k => $v) {
            if (is_numeric($v)) {
                echo sprintf('(%s:%.2f)', $k, $v);
            } else {
                echo "($k:$v)";
            }
        }
    } else {
        echo $name, ':', $line;
    }
    echo PHP_EOL;
}