<?php

namespace App\Controller;

use App\Common\RandcharToolkit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends BaseController
{
    const USER_FILE = '/dawn-sso/dawn-sso-api/var/user.txt'; // 该地址根据本地文件夹情况自定义

    const DOMAIN_1 = 'https://dawn.sso-client-1.cn';  // 客户端1地址
    const DOMAIN_2 = 'https://dawn.sso-client-2.cn';  // 客户端2地址

    public function checkLogin(Request $request)
    {
        $params = $request->request->all();
        if (empty($params['token'])) {
            return $this->fail('参数缺失');
        }

        $token = trim(\file_get_contents(self::USER_FILE));
        if ($token === $params['token']) {
            return $this->success(['uname' => '胡晓晓', 'ugender' => '女'], '已登录');
        }

        return $this->fail('认证错误');
    }

    public function login(Request $request)
    {
        $params = $request->request->all();
        if (empty($params['uname']) || empty($params['upwd'])) {
            return $this->fail('参数缺失');
        }

        if ('1' === $params['uname'] && '1' === $params['upwd']) {
            $token = RandcharToolkit::genChars(32, 7);
            \file_put_contents(self::USER_FILE, $token);

            return $this->success([
                'domainList' => [self::DOMAIN_1, self::DOMAIN_2],
                'token' => $token,
            ], '登录成功');
        }

        return $this->fail('登录失败');
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
