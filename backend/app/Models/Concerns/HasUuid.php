<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Gives a model a UUID primary key that is generated automatically on
 * creation, matching the `id UUID` primary key used by every entity in
 * the QueueLess database design.
 */
trait HasUuid
{
    public static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function initializeHasUuid(): void
    {
        $this->keyType = 'string';
        $this->incrementing = false;
    }
}
