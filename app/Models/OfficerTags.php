<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Spatie\Tags\Tag as SpatieTag;

class OfficerTags extends SpatieTag
{
    protected $table = 'tags';

    protected static function booted(): void
    {
        $type = 'officer-tag'; 

        if (auth()->check()) {
            static::addGlobalScope($type, function (Builder $builder) use ($type) {
                $builder->where('type', $type);
            });

            static::creating(function ($model) use ($type) {
                $model->type = $type;
            });
        }
    }
}
