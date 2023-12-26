<?php

namespace app;

use helper\WxToken;
use Httpful\Mime;
use Httpful\Request;

class Approval
{
    function get()
    {
        $f3 = \Base::instance();
        $token = new WxToken($f3->get('WXWORK_CORP_ID'), 'APPROVAL', $f3->get('WXWORK_CORP_SECRET'));
        $approvals = $this->getLeaveApprovalInfo($token->get(), $_GET['userid'], date('Y-m-01'), date('Y-m-d', strtotime('+1 day')));
        foreach ($approvals as $id) {
            echo "$id<br/>";
            $detail = $this->getApprovalDetail($token->get(), $id);
            echo '<pre style="margin-top:.1rem;margin-bottom:1rem;white-space:pre-wrap;word-wrap:break-word">', json_encode($detail->body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), '</pre>';
        }
    }

    //https://developer.work.weixin.qq.com/document/path/91816
    function getLeaveApprovalInfo($token, $userid, $start, $end)
    {
        $approvals = [];
        $cursor = '';
        $params = [
            'starttime' => strtotime($start),
            'endtime' => strtotime($end),
            'size' => 100,
            'filters' => [
                ['key' => 'creator', 'value' => $userid],
                ['key' => 'record_type', 'value' => '1'],//leave
            ]
        ];
        $response = $this->getApprovalInfo($token, $cursor, $params);
        $approvals = array_merge($approvals, $response->body->sp_no_list);
        while ($cursor = $response->body->new_next_cursor) {
            $response = $this->getApprovalInfo($token, $cursor, $params);
            $approvals = array_merge($approvals, $response->body->sp_no_list);
        }
        return $approvals;
    }

    function getApprovalInfo($token, $cursor, $params)
    {
        $params['new_cursor'] = $cursor;
        return Request::post(WxToken::API . '/cgi-bin/oa/getapprovalinfo?access_token=' . $token, json_encode($params))
            ->expectsType(Mime::JSON)
            ->send();
    }

    function getApprovalDetail($token, $id)
    {
        return Request::post(WxToken::API . '/cgi-bin/oa/getapprovaldetail?access_token=' . $token, json_encode(['sp_no' => $id]))
            ->expectsType(Mime::JSON)
            ->send();
    }
}