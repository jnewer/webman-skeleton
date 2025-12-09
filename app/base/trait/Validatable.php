<?php

namespace app\base\trait;

use think\Validate;

trait Validatable
{
    public function shouldUseChinese(): bool
    {
        $locale = config('translation.locale', 'zh_CN');
        return stripos($locale, 'zh') !== false || stripos($locale, 'cn') !== false;
    }

    protected function makeValidator(string $validateClass, string $scene): Validate
    {
        $validator = validate($validateClass)->scene($scene);

        if ($this->shouldUseChinese()) {
            $validator->useZh();
        }

        return $validator;
    }

    public function validate(string $validateClass, string $scene = '')
    {
        return $this->makeValidator($validateClass, $scene)->check($this->all());
    }

    public function validated(string $validateClass, string $scene = '')
    {
        return $this->makeValidator($validateClass, $scene)->checked($this->all());
    }
}
