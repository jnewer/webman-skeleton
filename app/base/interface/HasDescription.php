<?php

declare(strict_types=1);

namespace app\base\interface;

interface HasDescription
{
    public function getDescription(): ?string;
}
