<?php

declare(strict_types=1);

namespace app\base\trait;

use support\Response;


trait JsonResponse
{
    public function success(mixed $data = null, string $message = 'ok', int $code = Response::HTTP_OK): Response
    {
        return json([
            'status' => true,
            'code' => $code,
            'message' => $message,
            'data' => $data,
            'error' => null,
        ]);
    }

    public function error(string $message = '', int $code = Response::HTTP_BAD_REQUEST, ?array $error = null): Response
    {
        return json([
            'status' => false,
            'code' => $code,
            'message' => $message,
            'data' => null,
            'error' => $error,
        ]);
    }
}
