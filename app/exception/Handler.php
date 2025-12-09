<?php

namespace app\exception;

use Webman\Http\Request;
use Webman\Http\Response;
use Throwable;

class Handler extends \support\exception\Handler
{
    public $jsonRender = [];

    public function __construct($logger, $debug)
    {
        parent::__construct($logger, $debug);

        $this->dontReport = config('app.exception.dont_report', $this->dontReport);
        $this->jsonRender = config('app.exception.json_render', []);
    }

    public function render(Request $request, Throwable $exception): Response
    {
        if (in_array(get_class($exception), $this->jsonRender)) {
            $json = [
                'status' => false,
                'code' =>  $exception->getCode(),
                'message' => $exception->getMessage(),
            ];

            $this->debug && $json['traces'] = (string)$exception;

            return new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );
        }

        return parent::render($request, $exception);
    }
}
