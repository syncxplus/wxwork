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
        $dateRange = $f3->get('GET.dr') ?? '';
        switch ($dateRange) {
            case 1:
                $start = date('Y-m-d', strtotime('-30 days'));
                $end = date('Y-m-d');
                break;
            case 2:
                $start = date('Y-m-d', strtotime(date('Y-m-01') . ' -1 month'));
                $end = date('Y-m-d', strtotime(date('Y-m-01') . ' -1 day'));
                break;
            case 3:
                $start = date('Y-m-01');
                $end = date('Y-m-d');
                break;
            default:
                $start = $f3->get('GET.start') ?? date('Y-m-d', strtotime('-7 days'));
                $end = $f3->get('GET.end') ?? date('Y-m-d');
                if (strtotime($start) > strtotime($end)) {
                    $tmp = $start;
                    $start = $end;
                    $end = $tmp;
                }
        }
        $f3->set('dr', $dateRange);
        $userid = $_GET['userid'] ?: $f3->get('SESSION.USERID');
        $wx = new Wxwork($f3);
        if (empty($userid) || $userid == 'jibo') {
            $users = $wx->getUserList();
        } else {
            $users = [$wx->getUser($userid)];
        }
        $this->render($start, $end, $users);
    }

    function render($start, $end, $users)
    {
        $logger = new \Logger();
        $userid = array_column($users, 'userid');
        $query = $this->getCheckinData($start, $end, $userid);

        echo <<<HTML
        <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link href="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/4.6.0/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body class="container-fluid mb-5">
        <div style="display:flex;justify-content:space-between;margin:1rem 0;width:100%">
            <div><input class="form-control" name="start" value="$start"/></div>
            <div>
                <select class="form-control" name="date-range">
                    <option value=""></option>
                    <option value="1">近30天</option>
                    <option value="2">上月</option>
                    <option value="3">本月</option>
                </select>
            </div>
            <div><input class="form-control" name="end" value="$end"/></div>
        </div>
        HTML;

        echo '<table class="table">';
        foreach ($userid as $key => $user) {
            $raw = [];
            $stats = [];
            $logger->info($users[$key]->name);
            foreach ($query as $line) {
                if ($line->userid == $user) {
                    $logger->info(date('Y-m-d H:i', $line->checkin_time));
                    $logger->info($line->excetpion_type);
                    $date = date('Y-m-d', $line->checkin_time);
                    $prefix = date('G', $line->checkin_time) < 12 ? 'in' : 'out';
                    $e = $line->exception_type;
                    if ($e) {
                        $raw[] = json_encode($line, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                        if (!isset($stats[$date])) {
                            $stats[$date] = [];
                        }
                    }
                    $stats[$date][$prefix . '_exception'] = $e;
                    if ($e == '未打卡') {
                        $stats[$date][$prefix] = '';
                    } else {
                        $stats[$date][$prefix] = date('H:i', $line->checkin_time);
                    }
                }
            }
            $exception = false;
            foreach ($stats as $k => $v) {
                if (!$this->acceptTimeException($users[$key]->name, $k, $v)) {
                    if (!$exception) {
                        $exception = true;
                        echo "<tr class='table-warning'><td colspan='3'>", $users[$key]->name, "&nbsp;", $users[$key]->userid, '</td></tr>';
                    }
                    echo "<tr><td>$k</td><td>{$v['in']}<br/><span class='text-danger'>{$v['in_exception']}</span></td><td>{$v['out']}<br/><span class='text-danger'>{$v['out_exception']}</span></td></tr>";
                }
            }
            if ($exception && $_GET['debug']) echo '<tr><td class="font-weight-light text-muted" colspan="3"><pre style="white-space:pre-wrap;word-wrap:break-word">' . implode(PHP_EOL, $raw) . '</pre></td></tr>';
        }
        echo '</table>';

        if ($_GET['debug']) echo '<pre style="margin-top:1rem;margin-bottom:1rem;white-space:pre-wrap;word-wrap:break-word">', json_encode($users, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), '</pre>';

        $dr = \Base::instance()->get('dr');
        $queryStrDebug = $_GET['debug'] ? '&debug=1' : '';
        if (count($userid) == 1) {
            $queryStrUserid = "&userid=$userid[0]";
        } else {
            $queryStrUserid = "";
            echo <<<HTML
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll("tr.table-warning").forEach(function (e) {
            e.addEventListener("click", function () {
                let txt = e.innerText.split(/\s/);
                let userid = txt[1];
                if (userid) {
                    window.open("/Checkin?dr=" + document.querySelector("select").value + "&userid=" + userid + "&debug=1");
                }
            })
        })
    })
</script>
HTML;
        }
        echo <<<HTML
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                let select = document.querySelector("select");
                for (let i = 0; i < select.options.length; i++) {
                    if (select.options[i].value == "$dr") {
                        select.options[i].selected = true;
                        break;
                    }
                }
                let value = select.value;
                select.addEventListener("change", function (e) {
                    let v = e.target.value;
                    if (v != value) {
                        location.replace("/Checkin?dr=" + v + "$queryStrDebug$queryStrUserid");
                    }
                })
                document.querySelectorAll("input").forEach(function(e) {
                    e.addEventListener("keyup", function (k) {
                        if (k.key === "Enter") {
                            let start = document.querySelector("input[name=start]").value;
                            let end = document.querySelector("input[name=end]").value;
                            if (start != "$start" || end != "$end") {
                                location.replace("/Checkin?start=" + start + "&end=" + end);
                            }
                        }
                    })
                })
            })
        </script>
        </body>
        HTML;
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

    function acceptTimeException($name, $date, &$exception)
    {
        if (in_array($name, $this->ignore)) {
            return true;
        }
        $ie = $exception['in_exception'];
        $oe = $exception['out_exception'];
        if (!$ie && !$oe) {//no exception
            return true;
        } else if ($ie == '未打卡' || $oe == '未打卡') {//not time exception
            return false;
        }
        $in = str_replace(':', '', $exception['in']);
        $out = str_replace(':', '', $exception['out']);
        $rules = [];
        if ($userRule = $this->userRules[$name]) {
            $explode = explode('|', $userRule);
            foreach ($explode as $rule) {
                $rules[] = $rule;
            }
        }
        if ($dateRule = $this->dateRules[$date]) {
            $explode = explode('|', $dateRule);
            foreach ($explode as $rule) {
                $rules[] = $rule;
            }
        }
        $rules = array_unique($rules);
        foreach ($rules as $rule) {
            $threshold = explode('-', str_replace(':', '', $rule));
            if ($in > $threshold[0]) {
                $exception['in_exception'] = $exception['in_exception'] ?: 'c:时间异常';
            }  else {
                $exception['in_exception'] = '';
            }
            if ($out < $threshold[1]) {
                $exception['out_exception'] = $exception['out_exception'] ?: 'c:时间异常';
            } else {
                $exception['out_exception'] = '';
            }
            if ((strlen($exception['in_exception']) + strlen($exception['out_exception'])) == 0) {
                return true;
            }
        }
        return false;
    }

    private $userRules = [
        '曹晓锦' => '7:50-16:50',
        '周玉兰' => '9:30-18:00|9:00-17:30',
        '李莉媛' => '9:00-17:00',
    ];
    private $ignore = [
        '左婵',
    ];
    private $dateRules = [
        '2021-12-21' => '9:00-17:00',
    ];
}
