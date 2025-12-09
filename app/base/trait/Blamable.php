<?php

namespace app\base\trait;

trait Blamable
{
    public function creator()
    {
        return $this->hasOne(static::class, 'id', 'created_by');
    }

    public function updater()
    {
        return $this->hasOne(static::class, 'id', 'updated_by');
    }
}
