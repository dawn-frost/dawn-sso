<?php

namespace App\Controller;

use App\Common\RandcharToolkit;
use Symfony\Component\HttpFoundation\Request;
use Firebase\JWT\JWT;

class UserController extends BaseController
{
    const USER_LIST = [
        ['uname' => '1', 'upwd' => '1', 'uid' => '100001', 'urealname' => '胡晓晓1', 'ugender' => '女'],
        ['uname' => '2', 'upwd' => '2', 'uid' => '100002', 'urealname' => '胡晓晓2', 'ugender' => '男'],
    ];

    const JWT_SECRET_KEY = 'dqtBcibCiQYuOLq3';

    const DOMAIN_1 = 'https://dawn.sso-client-1.cn';  // 客户端1地址
    const DOMAIN_2 = 'https://dawn.sso-client-2.cn';  // 客户端2地址

    const TOKEN_FILE_DIR = '/Users/mac/Codes/dawn-sso/dawn-sso-api/var/';

    // 用于客户端请求验证是否登陆
    public function checkLogin(Request $request)
    {
        $params = $request->request->all();
        if (empty($params['token'])) {
            return $this->fail('参数缺失');
        }

        $fPath = self::TOKEN_FILE_DIR . $params['token'] . '.txt';
        if (!\file_exists($fPath)) {
            return $this->fail('已经退出登陆', ['logout' => true]);
        }

        $fInfos = \json_decode(\file_get_contents($fPath), true);
        $tInfos = (array) JWT::decode($params['token'], self::JWT_SECRET_KEY, ['HS256']);
        if ($fInfos['ticket'] !== $tInfos['ticket'] || $fInfos['timestamp'] !== $tInfos['timestamp'] || $fInfos['signer'] !== $tInfos['signer']) {
            return $this->fail('认证失败');
        }

        return $this->success(['uid' => $fInfos['uid'], 'urealname' => $fInfos['urealname'], 'ugender' => $fInfos['ugender']], '已登录');
    }

    // 用于客户端请求登陆验证
    public function login(Request $request)
    {
        $params = $request->request->all();
        if (empty($params['uname']) || empty($params['upwd'])) {
            return $this->fail('参数缺失');
        }

        $isAuth = false;
        $user = [];
        foreach (self::USER_LIST as $uitem) {
            if ($uitem['uname'] === $params['uname'] && $uitem['upwd'] === $params['upwd']) {
                $isAuth = true;
                $user = $uitem;
                break;
            }
        }

        if (!$isAuth) {
            return $this->fail('登录失败');
        }

        $ticket = RandcharToolkit::genChars(32, 7);

        $nTime = time();
        $info = [
            'ticket' => $ticket,
            'timestamp' => $nTime,
            'signer' => md5($ticket . $nTime . self::JWT_SECRET_KEY),
        ];

        $token = JWT::encode($info, self::JWT_SECRET_KEY);

        $info['token'] = $token;
        $info['uid'] = $user['uid'];
        $info['urealname'] = $user['urealname'];
        $info['ugender'] = $user['ugender'];

        \file_put_contents(self::TOKEN_FILE_DIR . $token . '.txt', \json_encode($info, \JSON_UNESCAPED_UNICODE));

        return $this->success([
            'domainList' => [self::DOMAIN_1, self::DOMAIN_2],
            'token' => $token,
        ], '登录成功');
    }

    // 用于客户端请求登出验证
    public function logout(Request $request)
    {
        $params = $request->query->all();
        if (empty($params['token'])) {
            return $this->fail('参数缺失');
        }

        $fPath = self::TOKEN_FILE_DIR . $params['token'] . '.txt';
        if (!\file_exists($fPath)) {
            return $this->success(['domainList' => [self::DOMAIN_1, self::DOMAIN_2], 'logout' => true], '已经退出登陆');
        }

        @\unlink($fPath);

        return $this->success(['domainList' => [self::DOMAIN_1, self::DOMAIN_2], 'logout' => true], '已经退出登陆');
    }
}
