<?php

declare(strict_types=1);

namespace app\base\interface;

interface HasLabel
{
    public function getLabel(): ?string;
}
