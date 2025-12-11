<?php

declare(strict_types=1);

namespace app\base\trait;

trait RequestMethodDetection
{
    public function isOptions()
    {
        return $this->method() === 'OPTIONS';
    }

    public function isDelete()
    {
        return $this->method() === 'DELETE';
    }

    public function isPut()
    {
        return $this->method() === 'PUT';
    }

    public function isPatch()
    {
        return $this->method() === 'PATCH';
    }

    public function isHead()
    {
        return $this->method() === 'HEAD';
    }
}
