<?php

namespace helper;

use Httpful\Mime;
use Httpful\Request;

class WxToken
{
    const API = 'https://qyapi.weixin.qq.com';
    const API_ACCESS_TOKEN = '/cgi-bin/gettoken';
    const CACHE_TOLERANCE = 300;

    private $corpId;
    private $secret;
    private $agentId;

    function __construct($corpId, $agentId, $secret)
    {
        $this->corpId = $corpId;
        $this->agentId = $agentId;
        $this->secret = $secret;
    }

    function get()
    {
        $f3 = \Base::instance();
        $logger = new \Logger();
        $key = 'TOKEN_' . $this->agentId;
        $accessToken = $f3->get($key);
        if (!$accessToken) {
            $response = Request::get(self::API . self::API_ACCESS_TOKEN . '?' . http_build_query(['corpid' => $this->corpId, 'corpsecret' => $this->secret]))
                ->expectsType(Mime::JSON)
                ->send();
            if ($response->body) {
                if ($response->body->errcode === 0) {
                    $accessToken = $response->body->access_token;
                    $expiresIn = $response->body->expires_in;
                    if (self::CACHE_TOLERANCE < $expiresIn) {
                        $f3->set($key, $accessToken, $expiresIn - self::CACHE_TOLERANCE);
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
}
