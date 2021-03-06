<?php

namespace App\Utility;

use EasySwoole\Component\Singleton;
use EasySwoole\HttpClient\Bean\Response;

class HttpClient
{
    use Singleton;

    /**
     * @desc
     * @author Huangbin <huangbin2018@qq.com>
     * @param string $url
     * @param array|null $params
     * @return Response
     * @throws \EasySwoole\HttpClient\Exception\InvalidUrl
     */
    public function post(string $url, ?array $params = null): Response
    {
        $client = new \EasySwoole\HttpClient\HttpClient();
        if (isset($params['timeout'])) {
            $client->setTimeout($params['timeout']);
        }
        if (isset($params['headers'])) {
            $client->setHeaders($params['headers']);
        }
        if (isset($params['cookies'])) {
            $client->addCookies($params['cookies']);
        }
        if (isset($params['form'])) {
            $client->post($params['form']);
        } elseif (isset($params['body'])) {
            $client->post($params['body']);
        } else {
            $client->post();
        }
        return $client->setUrl($url)->exec();
    }

    /**
     * @desc
     * @author Huangbin <huangbin2018@qq.com>
     * @param string $url
     * @param array|null $params
     * @return Response
     * @throws \EasySwoole\HttpClient\Exception\InvalidUrl
     */
    public function get(string $url, ?array $params = null): Response
    {
        $client = new \EasySwoole\HttpClient\HttpClient();
        if (isset($params['timeout'])) {
            $client->setTimeout($params['timeout']);
        }
        if (isset($params['headers'])) {
            $client->setHeaders($params['headers']);
        }
        if (isset($params['cookies'])) {
            $client->addCookies($params['cookies']);
        }
        if (isset($params['query'])) {
            $querys = http_build_query($params['query']);
            $urlQuery = parse_url($url, PHP_URL_QUERY);
            $url .= (empty($urlQuery) ? '?' : '&') . $querys;
        }
        return $client->setUrl($url)->exec();
    }
}
