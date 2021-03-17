<?php

namespace App\Common;

use GuzzleHttp\Client;

class GuzzleHttpToolkit
{
    protected static $_client;

    protected static function checkClient()
    {
        if (null === self::$_client) {
            $config = ['timeout' => 60, 'verify' => false];
            self::$_client = new Client($config);

            return self::$_client;
        }

        return self::$_client;
    }

    //发送表单字段请求
    public static function post(string $url, array $params, array $headers = [])
    {
        self::checkClient();

        $post = [];
        $post['form_params'] = $params;

        if (!empty($headers)) {
            $post['headers'] = $headers;
        }

        $body = self::$_client->request('POST', $url, $post)->getBody();

        return json_decode($body->getContents(), true);
    }

    //发送表单文件
    //$files 参数是直接把$_FILES拿过来即可
    public static function postFiles(string $url, array $files, array $headers = [])
    {
        $multiparts = [];
        foreach ($files as $key => $val) {
            $multiparts[] = [
                'name' => $key,
                'contents' => fopen($val['tmp_name'], 'r'),
                'filename' => $val['name'],
            ];
        }

        self::checkClient();

        $post = [];
        $post['multipart'] = $multiparts;

        if (!empty($headers)) {
            $post['headers'] = $headers;
        }

        $body = self::$_client->request('POST', $url, $post)->getBody();

        return json_decode($body->getContents(), true);
    }

    public static function postJson(string $url, array $params, array $headers = [])
    {
        self::checkClient();

        $post = [];
        $post['json'] = $params;

        if (!empty($headers)) {
            $post['headers'] = $headers;
        }

        $body = self::$_client->request('POST', $url, $post)->getBody();

        return json_decode($body->getContents(), true);
    }

    //查询字符串
    public static function get(string $url, array $params = [], array $headers = [])
    {
        self::checkClient();

        $get = [];
        $get['query'] = $params;

        if (!empty($headers)) {
            $get['headers'] = $headers;
        }

        $body = self::$_client->request('GET', $url, $get)->getBody();

        return json_decode($body->getContents(), true);
    }

    public static function delete(string $url, array $params = [], array $headers = [])
    {
        self::checkClient();

        if (!empty($headers)) {
            $params['headers'] = $headers;
        }

        $body = self::$_client->request('DELETE', $url, $params)->getBody();

        return json_decode($body->getContents(), true);
    }

    public static function put(string $url, array $params = [], array $headers = [])
    {
        self::checkClient();

        $put = [];
        $put['form_params'] = $params;

        if (!empty($headers)) {
            $put['headers'] = $headers;
        }

        $body = self::$_client->request('PUT', $url, $put)->getBody();

        return json_decode($body->getContents(), true);
    }
}
