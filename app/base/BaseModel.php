<?php

namespace app\base;

use support\Model;
use DateTimeInterface;
use app\base\BaseBuilder;

/**
 * @method static BaseBuilder query($method, $parameters)
 */
class BaseModel extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected $connection = 'mysql';

    protected static string $builder = BaseBuilder::class;

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
