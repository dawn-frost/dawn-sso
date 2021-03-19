<?php

namespace App\Controller;

use App\Common\GuzzleHttpToolkit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class UserController extends BaseController
{
    const DAWN_TOKEN = 'dawn-sso-login-token';

    public function checkLogin(Request $request)
    {
        $cookies = $request->cookies->all();
        if (!isset($cookies[self::DAWN_TOKEN])) {
            return $this->fail('未登录');
        }

        // 去sso认证
        $result = GuzzleHttpToolkit::post('https://dawn.sso-api.cn/check/login', ['token' => $cookies[self::DAWN_TOKEN]]);
        if ($result['success']) {
            return $this->success($result['data'], '已登录');
        }

        return $this->fail('登录认证未通过');
    }

    public function login(Request $request)
    {
        $response = $this->checkLogin($request);
        $rContent = \json_decode($response->getContent(), true);
        if ($rContent['success']) {
            return $response;
        }

        $params = $request->request->all();
        if (empty($params['uname']) || empty($params['upwd'])) {
            return $this->fail('参数错误');
        }

        // 去sso登录
        $result = GuzzleHttpToolkit::post('https://dawn.sso-api.cn/login', $params);
        if (true === $result['success']) {
            return $this->success($result['data'], '登录成功');
        }

        return $this->fail('登录失败');
    }

    public function addCookie(Request $request)
    {
        $params = $request->query->all();
        if (empty($params['token'])) {
            return $this->fail('参数错误');
        }

        return $this->addLoginCookie($params['token']);
    }

    protected function addLoginCookie(string $token)
    {
        $cookie = Cookie::create(self::DAWN_TOKEN, $token, 0, '/', null, true, true, false, 'none');

        $response = new Response();
        $response->headers->setCookie($cookie);

        return $response;
    }

    public function logout()
    {
        $cookie = Cookie::create(self::DAWN_TOKEN, null, 0, '/', null, true, true, false, 'none');

        $response = new Response();
        $response->headers->setCookie($cookie);

        return $response;
    }
}
