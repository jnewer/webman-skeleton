<?php

declare(strict_types=1);

namespace app\base\trait;

use support\Response;

trait ApiResponse
{
    public function success(mixed $data = null, string $message = 'ok', int $code = Response::HTTP_OK): Response
    {
        return json([
            'success' => true,
            'code' => $code,
            'message' => $message,
            'data' => $data,
            'error' => null,
        ]);
    }

    public function created(mixed $data = null, string $message = 'Created'): Response
    {
        return $this->success($data, $message, Response::HTTP_CREATED);
    }

    public function accepted(mixed $data = null, string $message = 'Accepted'): Response
    {
        return $this->success($data, $message, Response::HTTP_ACCEPTED);
    }

    public function noContent(string $message = 'No Content'): Response
    {
        return $this->success(null, $message, Response::HTTP_NO_CONTENT);
    }

    public function error(string $message = '', int $code = Response::HTTP_BAD_REQUEST, ?array $error = null): Response
    {
        return json([
            'success' => false,
            'code' => $code,
            'message' => $message,
            'data' => null,
            'error' => $error,
        ]);
    }

    public function badRequest(string $message = 'Bad Request', ?array $error = null): Response
    {
        return $this->error($message, Response::HTTP_BAD_REQUEST, $error);
    }

    public function unauthorized(string $message = 'Unauthorized', ?array $error = null): Response
    {
        return $this->error($message, Response::HTTP_UNAUTHORIZED, $error);
    }

    public function forbidden(string $message = 'Forbidden', ?array $error = null): Response
    {
        return $this->error($message, Response::HTTP_FORBIDDEN, $error);
    }

    public function notFound(string $message = 'Resource not found', ?array $error = null): Response
    {
        return $this->error($message, Response::HTTP_NOT_FOUND, $error);
    }

    public function methodNotAllowed(string $message = 'Method Not Allowed', ?array $error = null): Response
    {
        return $this->error($message, Response::HTTP_METHOD_NOT_ALLOWED, $error);
    }

    public function validationError(string $message = 'Validation failed', ?array $error = null): Response
    {
        return $this->error($message, Response::HTTP_UNPROCESSABLE_ENTITY, $error);
    }

    public function tooManyRequests(string $message = 'Too Many Requests', ?array $error = null): Response
    {
        return $this->error($message, Response::HTTP_TOO_MANY_REQUESTS, $error);
    }
}
