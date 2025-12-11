<?php

declare(strict_types=1);

namespace app\base;

use support\Db;
use support\Model;
use think\Validate;
use think\helper\Str;
use webman\Http\UploadFile;

class BaseValidate extends Validate
{
    /**
     * 验证是否唯一
     * @param mixed  $value 字段值
     * @param mixed  $rule  验证规则 格式：数据表,字段名,排除ID,主键名
     * @param array  $data  数据
     * @param string $field 验证字段名
     * @return bool
     */
    public function unique($value, $rule, array $data = [], string $field = ''): bool
    {
        if (is_string($rule)) {
            $rule = explode(',', $rule);
        }

        if (str_contains($rule[0], '\\')) {
            // 指定模型类
            $db = new $rule[0]();
        } else {
            $db = Db::table($rule[0]);
        }

        $key = $rule[1] ?? $field;
        $map = [];

        if (str_contains($key, '^')) {
            // 支持多个字段验证
            $fields = explode('^', $key);
            foreach ($fields as $key) {
                if (isset($data[$key])) {
                    $map[] = [$key, '=', $data[$key]];
                }
            }
        } elseif (strpos($key, '=')) {
            // 支持复杂验证
            parse_str($key, $array);
            foreach ($array as $k => $val) {
                $map[] = [$k, '=', $data[$k] ?? $val];
            }
        } elseif (isset($data[$field])) {
            $map[] = [$key, '=', $data[$field]];
        }

        $pk = !empty($rule[3]) ? $rule[3] : ($db instanceof Model ? $db->getKeyName() : 'id');

        if (is_string($pk)) {
            if (isset($rule[2])) {
                $map[] = [$pk, '<>', $rule[2]];
            } elseif (isset($data[$pk])) {
                $map[] = [$pk, '<>', $data[$pk]];
            }
        }

        if ($db->where($map)->first()) {
            return false;
        }

        return true;
    }

    /**
     * 验证字段值是否为有效格式
     * @param mixed  $value 字段值
     * @param string $rule  验证规则
     * @param array  $data  数据
     * @return bool
     */
    public function is($value, string $rule, array $data = []): bool
    {
        $call = function ($value, $rule) {
            if (function_exists('ctype_' . $rule)) {
                // ctype验证规则
                $ctypeFun = 'ctype_' . $rule;
                $result   = $ctypeFun((string) $value);
            } elseif (isset($this->filter[$rule])) {
                // Filter_var验证规则
                $result = $this->filter($value, $this->filter[$rule]);
            } else {
                // 正则验证
                $result = $this->regex($value, $rule);
            }
            return $result;
        };

        return match (Str::camel($rule)) {
            'require'         => !empty($value) || '0' == $value,
            'accepted'        => in_array($value, ['1', 'on', 'yes', 'true', 1, true], true),
            'declined'        => in_array($value, ['0', 'off', 'no', 'false', 0, false], true),
            'boolean', 'bool' => in_array($value, [true, false, 'true', 'false', 0, 1, '0', '1'], true),
            'date'            => false !== strtotime($value),
            'activeUrl'       => checkdnsrr($value),
            'number'          => is_numeric($value),
            'alphaNum'        => ctype_alnum((string)$value),
            'array'           => is_array($value),
            'integer', 'int'  => is_numeric($value) && is_int((int)$value),
            'float'           => is_numeric($value) && is_float((float)$value),
            'string'          => is_string($value),
            'file'            => $value instanceof UploadFile,
            'image'           => $value instanceof UploadFile && in_array($this->getImageType($value->getRealPath()), [1, 2, 3, 6, 9, 10, 11, 14, 15, 17, 18]),
            'token'           => $this->token($value, '__token__', $data),
            default           => $call($value, $rule),
        };
    }

    /**
     * 检测上传文件后缀
     * @param UploadFile         $file
     * @param array|string $ext 允许后缀
     * @return bool
     */
    protected function checkExt($file, $ext): bool
    {
        if (is_string($ext)) {
            $ext = explode(',', $ext);
        }

        return in_array(strtolower($file->getExtension()), $ext);
    }

    /**
     * 检测上传文件大小
     * @param UploadFile    $file
     * @param integer $size 最大大小
     * @return bool
     */
    protected function checkSize($file, $size): bool
    {
        return $file->getSize() <= (int) $size;
    }

    /**
     * 检测上传文件类型
     * @param UploadFile         $file
     * @param array|string $mime 允许类型
     * @return bool
     */
    protected function checkMime($file, $mime): bool
    {
        if (is_string($mime)) {
            $mime = explode(',', $mime);
        }

        return in_array(strtolower($file->getUploadMimeType()), $mime);
    }

    /**
     * 验证上传文件后缀
     * @param mixed $file 上传文件
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function fileExt($file, $rule): bool
    {
        if (is_array($file)) {
            foreach ($file as $item) {
                if (!($item instanceof UploadFile) || !$this->checkExt($item, $rule)) {
                    return false;
                }
            }
            return true;
        } elseif ($file instanceof UploadFile) {
            return $this->checkExt($file, $rule);
        }

        return false;
    }

    /**
     * 验证上传文件类型
     * @param mixed $file 上传文件
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function fileMime($file, $rule): bool
    {
        if (is_array($file)) {
            foreach ($file as $item) {
                if (!($item instanceof UploadFile) || !$this->checkMime($item, $rule)) {
                    return false;
                }
            }
            return true;
        } elseif ($file instanceof UploadFile) {
            return $this->checkMime($file, $rule);
        }

        return false;
    }

    /**
     * 验证上传文件大小
     * @param mixed $file 上传文件
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function fileSize($file, $rule): bool
    {
        if (is_array($file)) {
            foreach ($file as $item) {
                if (!($item instanceof UploadFile) || !$this->checkSize($item, $rule)) {
                    return false;
                }
            }
            return true;
        } elseif ($file instanceof UploadFile) {
            return $this->checkSize($file, $rule);
        }

        return false;
    }

    /**
     * 验证图片的宽高及类型
     * @param mixed $file 上传文件
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function image($file, $rule): bool
    {
        if (is_array($file)) {
            foreach ($file as $item) {
                if (!($item instanceof UploadFile) || !$this->checkImage($item, $rule)) {
                    return false;
                }
            }
            return true;
        } elseif ($file instanceof UploadFile) {
            return $this->checkImage($file, $rule);
        }

        return false;
    }

    /**
     * 验证数据长度
     * @param mixed                  $value 字段值
     * @param string|array|int|float $rule  验证规则
     * @return bool
     */
    public function length($value, $rule): bool
    {
        if (is_array($value)) {
            $length = count($value);
        } elseif ($value instanceof UploadFile) {
            $length = $value->getSize();
        } else {
            $length = mb_strlen((string) $value);
        }

        if (is_array($rule)) {
            // 长度区间
            return $length >= $rule[0] && $length <= $rule[1];
        } elseif (is_string($rule) && str_contains($rule, ',')) {
            // 长度区间
            [$min, $max] = explode(',', $rule);
            return $length >= $min && $length <= $max;
        }

        // 指定长度
        return $length == $rule;
    }

    /**
     * 验证数据最大长度
     * @param mixed $value 字段值
     * @param mixed $rule  验证规则
     * @return bool
     */
    public function max($value, $rule): bool
    {
        if (is_array($value)) {
            $length = count($value);
        } elseif ($value instanceof UploadFile) {
            $length = $value->getSize();
        } else {
            $length = mb_strlen((string) $value);
        }

        return $length <= $rule;
    }

    /**
     * 验证数据最小长度
     * @param mixed $value 字段值
     * @param mixed $rule  验证规则
     * @return bool
     */
    public function min($value, $rule): bool
    {
        if (is_array($value)) {
            $length = count($value);
        } elseif ($value instanceof UploadFile) {
            $length = $value->getSize();
        } else {
            $length = mb_strlen((string) $value);
        }

        return $length >= $rule;
    }
}
