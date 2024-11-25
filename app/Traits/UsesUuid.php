<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait UsesUuid
{
    protected $primaryKey = 'id';

    protected static function bootUsesUuid(): void
    {
        static::creating(function ($model) {
            if (! $model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function getIncrementing(): false
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }
}
