<?php

namespace app\admin\controller;

use support\Request;
use DI\Attribute\Inject;
use app\base\ApiController;
use app\admin\service\AuthService;

class AuthController extends ApiController
{
    #[Inject]
    private AuthService $authService;

    public function login(Request $request)
    {

        $tokenData = $this->authService->login(input('username'), input('password'), input('captcha_key'), input('captcha_code'));

        return $this->success($tokenData);
    }

    public function refreshToken(Request $request)
    {
        $tokenData = $this->authService->refreshToken();

        return $this->success($tokenData);
    }

    public function logout(Request $request)
    {
        return $this->success($this->authService->logout());
    }

    public function captcha(Request $request)
    {
        return $this->success($this->authService->captcha());
    }
}
