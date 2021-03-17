<?php

namespace App\Controller;

use App\Common\RandcharToolkit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UserController extends AbstractController
{
    const USER_FILE = '/Users/huxiaoyan/Codes/dawn-sso/dawn-sso-api/var/user.txt';

    const DOMAIN_1 = 'https://dawn.sso-client-1.cn';
    const DOMAIN_2 = 'https://dawn.sso-client-2.cn';

    public function checkLogin(Request $request)
    {
        $params = $request->request->all();
        if (empty($params['token'])) {
            return new JsonResponse(['success' => false,
                'message' => '参数错误',
                'code' => 0,
                'data' => [], ]);
        }

        $token = trim(\file_get_contents(self::USER_FILE));
        if ($token === $params['token']) {
            return new JsonResponse(['success' => true,
                'message' => '已登陆',
                'code' => 0,
                'data' => ['uname' => '胡晓晓', 'ugender' => '女'], ]);
        }

        return new JsonResponse(['success' => false,
            'message' => '认证错误',
            'code' => 0,
            'data' => [], ]);
    }

    public function login(Request $request)
    {
        $params = $request->request->all();
        if (empty($params['uname']) || empty($params['upwd'])) {
            return new JsonResponse(['success' => false,
                'message' => '参数错误',
                'code' => 0,
                'data' => [], ]);
        }

        if ('1' === $params['uname'] && '1' === $params['upwd']) {
            $token = RandcharToolkit::genChars(32, 7);
            \file_put_contents(self::USER_FILE, $token);

            return new JsonResponse(['success' => true,
                'message' => '登陆成功',
                'code' => 1,
                'data' => [
                    'domainList' => [self::DOMAIN_1, self::DOMAIN_2],
                    'token' => $token,
                ], ]);
        }

        return new JsonResponse(['success' => false,
            'message' => '登陆失败',
            'code' => 0,
            'data' => [], ]);
    }

    public function logout(Request $request)
    {
        $params = $request->query->all();

        \file_put_contents(self::USER_FILE, '');

        $domainList = \json_encode([self::DOMAIN_1, self::DOMAIN_2], \JSON_UNESCAPED_UNICODE);

        $response = new Response($params['callback'] . '(' . $domainList . ')');

        return $response;
    }
}
