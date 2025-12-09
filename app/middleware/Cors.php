<?php

namespace app\middleware;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;

class Cors implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        $response = $request->isPost() ? response('') : $handler($request);

        $response->withHeaders([
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Origin' => $request->header('origin', config('cors.allow_origin', '*')),
            'Access-Control-Allow-Methods' => $request->header('access-control-request-method', config('cors.allow_methods', '*')),
            'Access-Control-Allow-Headers' => $request->header('access-control-request-headers', config('cors.allow_headers', '*')),
        ]);

        return $response;
    }
}
