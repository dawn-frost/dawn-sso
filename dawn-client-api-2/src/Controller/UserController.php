<?php

namespace App\Controller;

use App\Common\GuzzleHttpToolkit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UserController extends AbstractController
{
    const DAWN_TOKEN = 'dawn-sso-login-token';

    public function checkLogin(Request $request)
    {
        $cookies = $request->cookies->all();

        if (!isset($cookies[self::DAWN_TOKEN])) {
            return new JsonResponse(['success' => false,
                'message' => '未登陆',
                'code' => 0,
                'data' => [], ]);
        }

        // 去sso认证
        $result = GuzzleHttpToolkit::post('https://dawn.sso-api.cn/check/login', ['token' => $cookies[self::DAWN_TOKEN]]);
        if ($result['success']) {
            return new JsonResponse(['success' => true,
                'message' => '已登陆',
                'code' => 0,
                'data' => $result['data'], ]);
        }

        return new JsonResponse(['success' => false,
            'message' => '登陆认证未通过',
            'code' => 0,
            'data' => [], ]);
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
            return new JsonResponse(['success' => false,
                'message' => '参数错误',
                'code' => 0,
                'data' => [], ]);
        }

        // 去sso登陆
        $result = GuzzleHttpToolkit::post('https://dawn.sso-api.cn/login', $params);
        if (true === $result['success']) {
            return new JsonResponse(['success' => true,
                'message' => '登陆成功',
                'code' => 1,
                'data' => $result['data'], ]);
        }

        return new JsonResponse(['success' => false,
            'message' => '登陆失败',
            'code' => 0,
            'data' => [], ]);
    }

    public function addCookie(Request $request)
    {
        $params = $request->query->all();
        if (empty($params['token'])) {
            return new JsonResponse(['success' => false,
                'message' => '参数错误',
                'code' => 0,
                'data' => [], ]);
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
