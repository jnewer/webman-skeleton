<?php

namespace app\base\enum;

use app\base\interface\HasLabel;
use app\base\trait\ArrayableEnum;

enum Status: int implements HasLabel
{
    use ArrayableEnum;

    case DISABLED = 0;
    case ENABLED = 1;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DISABLED => '禁用',
            self::ENABLED => '启用',
        };
    }
}
