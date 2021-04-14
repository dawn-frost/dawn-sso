<?php

namespace App\Controller;

use App\Common\GuzzleHttpToolkit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class UserController extends BaseController
{
    const DAWN_TOKEN = 'dawn-sso-login-token';

    const SSL_HOST = 'https://dawn.sso-api.cn';

    public function checkLogin(Request $request)
    {
        $cookies = $request->cookies->all();
        if (!isset($cookies[self::DAWN_TOKEN])) {
            return $this->fail('未登录');
        }

        // 去sso认证
        $result = GuzzleHttpToolkit::post(self::SSL_HOST . '/check/login', ['token' => $cookies[self::DAWN_TOKEN]]);
        if (true === $result['success']) {
            return $this->success($result['data'], '已登录');
        }

        return $this->fail($result['message'], $result['data']);
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
        $result = GuzzleHttpToolkit::post(self::SSL_HOST . '/login', $params);
        if (true === $result['success']) {
            return $this->success($result['data'], '登录成功');
        }

        return $this->fail($result['message']);
    }

    public function logout(Request $request)
    {
        $cookies = $request->cookies->all();
        if (!isset($cookies[self::DAWN_TOKEN])) {
            return $this->fail('已退出登录');
        }

        // 去sso退出登陆
        $result = GuzzleHttpToolkit::get(self::SSL_HOST . '/logout', ['token' => $cookies[self::DAWN_TOKEN]]);
        if (true === $result['success']) {
            return $this->success($result['data'], '退出登陆成功');
        }

        return $this->fail($result['message']);
    }

    public function addCookie(Request $request)
    {
        $params = $request->query->all();
        if (empty($params['token'])) {
            return $this->fail('参数错误');
        }

        // todo：这里最好再去sso-api中验证一下token是否真的存在

        // 为了兼容“不兼容samesite”的浏览器而设置两种cookie
        $cookie1 = Cookie::create(self::DAWN_TOKEN, $params['token'], 0, '/', null, true, true, false, 'none');
        $cookie2 = Cookie::create(self::DAWN_TOKEN, $params['token'], 0, '/', null, true, true, false, null);

        $response = new Response();
        $response->headers->setCookie($cookie2);
        $response->headers->setCookie($cookie1);

        return $response;
    }

    public function clearCookie()
    {
        // 为了兼容“不兼容samesite”的浏览器而设置两种cookie
        $cookie2 = Cookie::create(self::DAWN_TOKEN, null, 0, '/', null, true, true, false, null);
        $cookie1 = Cookie::create(self::DAWN_TOKEN, null, 0, '/', null, true, true, false, 'none');

        $response = new Response();
        $response->headers->setCookie($cookie2);
        $response->headers->setCookie($cookie1);

        return $response;
    }
}
