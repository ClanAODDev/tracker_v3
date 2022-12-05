<?php

namespace App\Transformers;

use Illuminate\Support\Str;

class DivisionBasicTransformer extends Transformer
{
    public function transform($item): array
    {
        return [
            'name' => $item->name,
            'slug' => Str::slug($item->name),
            'abbreviation' => $item->abbreviation,
            'description' => $item->description,
            'forum_app_id' => $item->forum_app_id,
            'members_count' => $item->members_count,
        ];
    }
}
