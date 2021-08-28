<?php

namespace helper;

use Httpful\Mime;
use Httpful\Request;

class Wxwork
{
    static private $CACHE_TOLERANCE = 300;
    static private $API = 'https://qyapi.weixin.qq.com';
    static private $GET_ACCESS_TOKEN = '/cgi-bin/gettoken';
    static private $GET_JS_TICKET = '/cgi-bin/get_jsapi_ticket';
    static private $GET_USER_INFO = '/cgi-bin/user/getuserinfo';
    static private $GET_USER_LIST = '/cgi-bin/user/list';
    static private $OAUTH2 = 'https://open.weixin.qq.com/connect/oauth2/authorize';
    private $f3;
    private $corpId;
    private $corpSecret;
    private $agentId;

    function __construct($f3)
    {
        $this->f3 = $f3;
        $this->corpId = $f3->WXWORK_CORP_ID;
        $this->corpSecret = $f3->WXWORK_CORP_SECRET;
        $this->agentId = $f3->WXWORK_AGENT_ID;
    }

    function getAccessToken()
    {
        $logger = new \Logger();
        $accessToken = $this->f3->WXWORK_ACCESS_TOKEN;
        if (!$accessToken) {
            $response = Request::get(self::$API . self::$GET_ACCESS_TOKEN . '?' . http_build_query(['corpid' => $this->corpId, 'corpsecret' => $this->corpSecret]))
                ->expectsType(Mime::JSON)
                ->send();
            if ($response->body) {
                if ($response->body->errcode === 0) {
                    $accessToken = $response->body->access_token;
                    $expiresIn = $response->body->expires_in;
                    if (self::$CACHE_TOLERANCE < $expiresIn) {
                        $this->f3->set('WXWORK_ACCESS_TOKEN', $accessToken, $expiresIn - self::$CACHE_TOLERANCE);
                    }
                } else {
                    $logger->error('Failed to get access_token, (%d: %s)', [$response->body->errcode, $response->body->errmsg]);
                }
            } else {
                $logger->error('Failed to get access_token');
                ob_start();
                var_dump($response);
                $logger->error(ob_get_clean());
            }
        }
        return $accessToken;
    }

    function getJsTicket()
    {
        $logger = new \Logger();
        $jsTicket = $this->f3->WXWORK_JS_TICKET;
        if (!$jsTicket) {
            $response = Request::get(self::$API . self::$GET_JS_TICKET . '?' . http_build_query(['access_token' => $this->getAccessToken()]))
                ->expectsType(Mime::JSON)
                ->send();
            if ($response->body) {
                if ($response->body->errcode === 0) {
                    $jsTicket = $response->body->ticket;
                    $expiresIn = $response->body->expires_in;
                    if (self::$CACHE_TOLERANCE < $expiresIn) {
                        $this->f3->set('WXWORK_JS_TICKET', $jsTicket, $expiresIn - self::$CACHE_TOLERANCE);
                    }
                } else {
                    $logger->error('Failed to get ticket, (%d: %s)', [$response->body->errcode, $response->body->errmsg]);
                }
            } else {
                $logger->error('Failed to get ticket');
                ob_start();
                var_dump($response);
                $logger->error(ob_get_clean());
            }
        }
        return $jsTicket;
    }

    function getJsConfig($url)
    {
        $nonce = strtolower(bin2hex(random_bytes('5')));
        $timestamp = time();
        $ticket = $this->getJsTicket();

        $config = [
            'appId' => $this->corpId,
            'nonceStr' => $nonce,
            'timestamp' => $timestamp,
            'url' => $url,
            'signature' => sha1("jsapi_ticket={$ticket}&noncestr={$nonce}&timestamp={$timestamp}&url={$url}"),
        ];

        return $config;
    }

    function getUserInfo($code, $refresh = false)
    {
        $logger = new \Logger();
        $userInfo = $this->f3->WXWORK_USER_INFO;
        if (!$userInfo || $refresh) {
            $response = Request::get(self::$API . self::$GET_USER_INFO . '?' . http_build_query(['access_token' => $this->getAccessToken(), 'code' => $code]))
                ->expectsType(Mime::JSON)
                ->send();
            if ($response->body) {
                if ($response->body->errcode === 0) {
                    $userInfo = $response->body;
                    $expiresIn = $response->body->expires_in ?: 7200;
                    if (self::$CACHE_TOLERANCE < $expiresIn) {
                        $this->f3->set('WXWORK_USER_INFO', $userInfo, $expiresIn - self::$CACHE_TOLERANCE);
                    }
                } else {
                    $logger->error('Failed to get user info, (%d: %s)', [$response->body->errcode, $response->body->errmsg]);
                }
            } else {
                $logger->error('Failed to get user info');
                ob_start();
                var_dump($response);
                $logger->error(ob_get_clean());
            }
        }
        return $userInfo;
    }

    function getUserList()
    {
        $logger = new \Logger();
        $response = Request::get(self::$API . self::$GET_USER_LIST . '?' . http_build_query([
                'access_token' => $this->getAccessToken(),
                'department_id' => 7,
                'fetch_child' => 1,
            ]))
            ->expectsType(Mime::JSON)
            ->send();
        if ($response->body) {
            if ($response->body->errcode === 0) {
                $logger->info('Get user list no error');
                return $response->body->userlist;
            } else {
                $logger->error('Failed to get user list, (%d: %s)', [$response->body->errcode, $response->body->errmsg]);
            }
        } else {
            $logger->error('Failed to get user list');
            ob_start();
            var_dump($response);
            $logger->error(ob_get_clean());
        }
        $logger->info('Return empty user list');
        return [];
    }

    function getOauth2Url($callback, $scope = 'snaapi_userinfo', $state = '')
    {
        $data = [
            'appid' => $this->corpId,
            'redirect_uri' => urlencode($callback),
            'response_type' => 'code',
            'scope' => $scope
        ];
        if ($scope != 'snsapi_base') {
            $data['agentid'] = $this->agentId;
        }
        if (!empty($state)) {
            $data['state'] = $state;
        }
        return self::$OAUTH2 . '?' . http_build_query($data) . '#wechat_redirect';
    }
}
