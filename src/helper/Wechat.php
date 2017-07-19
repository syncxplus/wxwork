<?php

namespace helper;

use Httpful\Mime;
use Httpful\Request;

class Wechat
{
    static private $CACHE_TOLERANCE = 300;
    static private $API = 'https://qyapi.weixin.qq.com';
    static private $GET_ACCESS_TOKEN = '/cgi-bin/gettoken';
    static private $GET_JS_TICKET = '/cgi-bin/get_jsapi_ticket';
    private $f3;
    private $corpId;
    private $corpSecret;

    function __construct($f3)
    {
        $this->f3 = $f3;
        $this->corpId = $f3->WXWORK_CORP_ID;
        $this->corpSecret = $f3->WXWORK_CORP_SECRET;
    }

    function getAccessToken()
    {
        $logger = new Logger($this->f3->LOGS);
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
        $logger = new Logger($this->f3->LOGS);
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
}
