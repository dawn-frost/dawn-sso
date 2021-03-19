<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class BaseController extends AbstractController
{
    protected function success(array $data = [], string $message = '请求成功'): JsonResponse
    {
        $data = [
            'success' => true,
            'message' => $message,
            'code' => 0,
            'data' => $data,
        ];

        return new JsonResponse($data);
    }

    protected function fail(string $message, array $data = []): JsonResponse
    {
        $data = [
            'success' => false,
            'message' => $message,
            'code' => 0,
            'data' => $data,
        ];

        return new JsonResponse($data);
    }
}
