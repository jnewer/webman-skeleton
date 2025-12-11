<?php

declare(strict_types=1);

namespace app\base\exception;

use Error;

final class UndefinedEnumCaseError extends Error
{
    /**
     * @param  class-string  $enum
     * @return void
     */
    public function __construct(string $enum, string $case)
    {
        parent::__construct(
            message: "Undefined constant {$enum}::{$case}.",
        );
    }
}
