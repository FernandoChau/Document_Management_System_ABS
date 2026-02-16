<?php

namespace App\Models\Traits;

use Illuminate\Support\Str;

trait UuidTrait
{
    /**
     * Boot the trait.
     * Automatically generate a UUID for the model's primary key.
     */
    protected static function bootUuidTrait()
    {
        static::creating(function ($model) {
            if (!$model->{$model->getKeyName()}) {
                $model->{$model->getKeyName()} = Str::uuid()->toString();
            }
        });
    }
}
