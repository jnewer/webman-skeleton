<?php

namespace app\admin\service;

use support\Redis;
use Tinywan\Jwt\JwtToken;
use app\admin\model\system\User;
use Webman\Captcha\CaptchaBuilder;
use think\exception\ValidateException;
use support\exception\BusinessException;

class AuthService
{
    public function login(string $username, string $password, string $captchaKey, string $captchaCode): array
    {
        $storedCaptcha = Redis::get($captchaKey);
        if (!$storedCaptcha || strtolower($captchaCode) !== $storedCaptcha) {
            throw new ValidateException('验证码错误或已过期');
        }

        Redis::del($captchaKey);

        $user = User::where('username', $username)->first();
        if (!$user || !password_verify($password, $user->password)) {
            throw new ValidateException('用户名或密码错误');
        }

        return  JwtToken::generateToken([
            'id' => $user->id,
            'username' => $user->username,
            'nickname' => $user->nickname,
            'email' => $user->email,
        ]);
    }

    public function logout()
    {
        return JwtToken::clear();
    }

    public function captcha(string $client = JwtToken::TOKEN_CLIENT_WEB): array
    {
        $builder = new CaptchaBuilder;
        $builder->build();
        $captchaKey = $client . '.captcha_' . uniqid();
        Redis::setex($captchaKey, 300, strtolower($builder->getPhrase()));

        return [
            'captcha_key' => $captchaKey,
            'captcha_base64' => 'data:image/jpeg;base64,' . base64_encode($builder->get()),
            'captcha_phrase' => $builder->getPhrase(),
        ];
    }

    public function refreshToken(): array
    {
        return JwtToken::refreshToken();
    }
}
