<?php

use Webman\Route;
use app\admin\controller\AuthController;
use app\admin\controller\system\UserController;

Route::group('/api/v1', function () {
    Route::group('/auth', function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
        Route::delete('/logout', [AuthController::class, 'logout']);
        Route::get('/captcha', [AuthController::class, 'captcha']);
    });

    Route::group('/users', function () {
        Route::get('/me', [UserController::class, 'me']);
        Route::put('/password', [UserController::class, 'resetPassword']);
    });

    Route::resource('/users', UserController::class, ['index', 'store', 'update', 'show', 'destroy']);
});
