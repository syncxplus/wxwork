<?php

namespace app;

use helper\WxToken;
use helper\Wxwork;
use Httpful\Mime;
use Httpful\Request;

class Checkin
{
    function get($f3)
    {
        $start = $f3->get('GET.start') ?? date('Y-m-d', strtotime('-7 days'));
        $end = $f3->get('GET.end') ?? date('Y-m-d');

        $users = (new Wxwork($f3))->getUserList();
        $userid = array_column($users, 'userid');
        array_multisort($userid, $users);

        echo <<<HTML
        <head>
        <link href="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/4.6.0/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body class="container mt-1 mb-5">
        <div style="font-size: 2rem"><span>$start</span><span style="float: right">$end</span></div>
        <table class="table">
        HTML;

        $logger = new \Logger();
        $checkin = new Checkin();
        $query = $checkin->getCheckinData($start, $end, $userid);
        foreach ($userid as $key => $user) {
            $stats = [];
            $logger->info($users[$key]->name);
            foreach ($query as $line) {
                if ($line->userid == $user) {
                    $logger->info(date('Y-m-d H:m', $line->checkin_time));
                    $logger->info($line->excetpion_type);
                    $date = date('Y-m-d', $line->checkin_time);
                    if (!isset($stats[$date])) {
                        $stats[$date] = [];
                    }
                    $prefix = date('G', $line->checkin_time) <= 12 ? 'in' : 'out';
                    $e = $line->exception_type;
                    $stats[$date][$prefix . '_exception'] = $e;
                    if ($e == '未打卡') {
                        $stats[$date][$prefix] = '';
                    } else {
                        $stats[$date][$prefix] = date('H:m', $line->checkin_time);
                    }
                }
            }
            $exception = false;
            foreach ($stats as $k => $v) {
                if (!$this->acceptTimeException($users[$key]->name, $v)) {
                    if (!$exception) {
                        $exception = true;
                        echo "<tr class='bg-secondary text-light'><td colspan='5'>" . $users[$key]->name . '</td></tr>';
                    }
                    echo "<tr><td>$k</td><td>{$v['in']}</td><td>{$v['in_exception']}</td><td>{$v['out']}</td><td>{$v['out_exception']}</td></tr>";
                }
            }
        }

        echo '</table></body>';
    }

    function getCheckinData($start, $end, $users)
    {
        $f3 = \Base::instance();
        $logger = new \Logger();
        $token = new WxToken($f3->get('WXWORK_CORP_ID'), $f3->get('WXWORK_CHECKIN_ID'), $f3->get('WXWORK_CHECKIN_SECRET'));
        $response = Request::post(WxToken::API . '/cgi-bin/checkin/getcheckindata?access_token=' . $token->get(), json_encode([
            'opencheckindatatype' => 3,
            'starttime' => strtotime($start),
            'endtime' => strtotime("$end + 1day"),
            'useridlist' => $users,
        ]))
            ->expectsType(Mime::JSON)
            ->send();
        if ($response->body) {
            if ($response->body->errcode === 0) {
                $logger->info('Get checkin data no error');
                return $response->body->checkindata;
            } else {
                $logger->error('Failed to get checkin data, (%d: %s)', [$response->body->errcode, $response->body->errmsg]);
            }
        } else {
            $logger->error('Failed to get checkin data');
            ob_start();
            var_dump($response);
            $logger->error(ob_get_clean());
        }
        $logger->info('Return empty checkin data');
        return [];
    }

    function acceptTimeException($name, $exception)
    {
        $ie = $exception['in_exception'];
        $oe = $exception['out_exception'];
        if (!$ie && !$oe) {//no exception
            return true;
        } else if ($ie == '未打卡' || $oe == '未打卡') {//not time exception
            return false;
        }
        $rules = [
            '曹晓锦' => '8:00-17:00',
            '周玉兰' => '9:30-18:00|9:00-17:30',
        ];
        $rule = $rules[$name];
        if (!$rule) {//no rule for the time exception
            return false;
        }
        $in = str_replace(':', '', $exception['in']);
        $out = str_replace(':', '', $exception['out']);
        $options = explode('|', $rule);
        foreach ($options as $option) {
            $threshold = explode('-', str_replace(':', '', $option));
            return $in <= $threshold[0] && $out >= $threshold[1];
        }
        return false;
    }
}
